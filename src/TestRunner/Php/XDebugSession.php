<?php
/**
 * Copyright (c) 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details. You should have received a copy of the GNU Lesser General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\TestRunner\Php;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\Php\PhpAssertionInterface;
use JDWil\Unify\Assertion\Php\PhpAssertionQueue;
use JDWil\Unify\Exception\XdebugException;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\XDebugResponse;
use JDWil\Unify\TestRunner\TestPlanInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class XDebugSession
 */
class XDebugSession extends AbstractSession
{
    /**
     * @var PhpTestPlan
     */
    protected $testPlan;

    /**
     * @var int
     */
    private $transaction;

    /**
     * @var bool
     */
    private $debuggerStopped;

    /**
     * @var array
     */
    private $initializationCommands;

    /**
     * @var PhpAssertionQueue
     */
    private $assertions;

    /**
     * @var PhpAssertionQueue
     */
    private $assertionQueue;

    /**
     * @var array
     */
    private $commandStack;

    /**
     * @var int
     */
    private $currentAssertionNumber;

    /**
     * @var array
     */
    private $iterations;

    /**
     * @var \DOMElement
     */
    private $lastRunningResponse;

    /**
     * @var array
     */
    private $processedAssertions;

    /**
     * @var int
     */
    private $continuingFromLine;

    /**
     * @var string
     */
    private $lastResponse;

    /**
     * @var array
     */
    private $processedFailures;

    /**
     * @var bool
     */
    private $generateCodeCoverage;

    /**
     * @var array
     */
    private $coverageGathered;

    /**
     * @var array
     */
    private $coverage;

    /**
     * DebugSession constructor.
     * @param string $host
     * @param int $port
     * @param OutputInterface $output
     * @param bool $generateCodeCoverage
     */
    public function __construct($host, $port, OutputInterface $output, $generateCodeCoverage = false)
    {
        parent::__construct($output, $host, $port);

        $this->transaction = 1;
        $this->debuggerStopped = false;
        $this->iterations = [];
        $this->processedAssertions = [];
        $this->processedFailures = [];
        $this->coverageGathered = [];
        $this->coverage = [];
        $this->generateCodeCoverage = $generateCodeCoverage;

        $this->setContext(self::INITIALIZE);

        $this->initializationCommands = [
            "feature_set -i %d -n show_hidden -v 1\0",
            "feature_set -i %d -n max_children -v 100\0",
            "feature_set -i %d -n max_data -v 100000\0",
            "stdout -i %d -c 2\0",
            "step_into -i %d\0",
        ];

        if ($generateCodeCoverage) {
            /* eval xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE); */
            $this->initializationCommands[] =
                'eval -i %d -- ' .
                "eGRlYnVnX3N0YXJ0X2NvZGVfY292ZXJhZ2UoWERFQlVHX0NDX1VOVVNFRCB8IFhERUJVR19DQ19ERUFEX0NPREUpOw==\0";
        }
    }

    /**
     * @param TestPlanInterface $testPlan
     * @return PhpAssertionQueue
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \JDWil\Unify\Exception\XdebugException
     * @throws \LogicException
     */
    public function execute(TestPlanInterface $testPlan)
    {
        $this->testPlan = $testPlan;
        $this->assertions = $testPlan->getAssertionQueue();
        foreach ($this->testPlan->getCommands() as $command) {
            $this->initializationCommands[] = $command;
        }

        $this->bootSocketServer();

        $this->socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                $responses = $this->parseResponse($data);

                foreach ($responses as $xml) {
                    if ($this->output->isDebug() && strlen($xml) < 2048) {
                        $this->output->writeln($xml);
                    }
                    $document = new \DOMDocument();
                    $document->loadXML($xml);

                    if ($document->documentElement->localName === 'stream' &&
                        $document->documentElement->getAttribute('type') === 'stdout'
                    ) {
                        $output = base64_decode($document->documentElement->nodeValue);
                        $this->debugOutput('STDOUT:');
                        $this->debugOutput($output);
                        $this->testPlan->appendOutput($output);
                    } else if ($this->context() === self::COVERAGE) {
                        $php = $document->documentElement->firstChild->nodeValue;
                        $php = base64_decode($php);
                        $this->coverage = eval(sprintf("return $php;"));
                        $this->popContext();
                        $this->debug($this->lastRunningResponse, $connection);
                    } else {
                        $this->lastResponse = $xml;
                        $this->debug($document->documentElement, $connection);
                    }
                }
            });
        });

        $this->loop->addTimer(0.001, function () {
            $command = $this->buildRunCommand($this->testPlan);
            $process = new Process($command);
            $process->start();
        });

        $this->loop->run();

        return $this->assertions;
    }

    /**
     * @return array
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * @param \DOMElement $response
     * @param ConnectionInterface $connection
     * @throws \JDWil\Unify\Exception\XdebugException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    private function debug(\DOMElement $response, ConnectionInterface $connection)
    {
        $this->handleError($response);

        if ($response->getAttribute('status') === 'stopping') {
            $this->debugOutput('  Entering postmortem');
            $this->debuggerStopped = true;
            $this->setContext(self::POSTMORTEM);
        }

        if ($response->firstChild && $response->firstChild->localName === 'message') {
            $line = (int) $response->firstChild->getAttribute('lineno');
            $this->bumpIterationCount($line);
            $this->debugOutput(sprintf('  Broke on line %d', $line));

            if ($this->generateCodeCoverage &&
                !isset($this->coverageGathered[$line]) &&
                in_array($this->context(), [self::RUNNING, self::CONTINUING], true)
            ) {
                $this->removeIteration($line);
                $this->lastRunningResponse = $response;
                /* eval var_export(xdebug_get_code_coverage(), true) */
                $this->send($connection, "eval -i %d -- dmFyX2V4cG9ydCh4ZGVidWdfZ2V0X2NvZGVfY292ZXJhZ2UoKSwgdHJ1ZSk7\0");
                $this->pushContext(self::COVERAGE);
                $this->coverageGathered[$line] = true;
                return;
            }
        }

        if ($this->context() === self::CONTINUING) {
            if (!isset($line)) {
                throw new \LogicException('Line is not set.');
            }

            if ($line === $this->continuingFromLine) {
                $this->send($connection, "step_over -i %s\0");
                return;
            }

            $this->popContext();
        }

        switch ($this->context()) {
            case self::INITIALIZE:
                $this->handleInitializationMode($connection);
                break;

            case self::RUNNING:
                $this->handleRunMode($response, $connection);
                break;

            case self::ASSERTING:
                $this->handleAssertingMode($response, $connection);
                break;

            case self::POSTMORTEM:
                $this->handlePostmortemMode($connection);
                break;

            case self::STOPPED:
                $this->stop($connection);
                break;
        }
    }

    /**
     * @param ConnectionInterface $connection
     */
    protected function handleInitializationMode(ConnectionInterface $connection)
    {
        $command = array_shift($this->initializationCommands);
        if (empty($this->initializationCommands)) {
            $this->setContext(self::RUNNING);
        }

        $this->send($connection, $command);
    }

    /**
     * @param \DOMElement $response
     * @param ConnectionInterface $connection
     * @throws \LogicException
     */
    protected function handleRunMode(\DOMElement $response, ConnectionInterface $connection)
    {
        $line = (int) $response->firstChild->getAttribute('lineno');
        $assertions = $this->getUnprocessedAssertions($line, $this->iterationCount($line));
        if ($assertions && !$assertions->isEmpty()) {
            $this->debugOutput(sprintf('  Found assertions for line %d, iteration %d', $line, $this->iterationCount($line)));
            $this->assertionQueue = $assertions;
            $this->pushContext(self::ASSERTING);
            if ($current = $this->assertionQueue->current()) {
                /** @var PhpAssertionInterface $current */
                $this->commandStack = $current->getDebuggerCommands();
            } else {
                throw new \LogicException('assertionQueue::current() returned null');
            }
            $this->currentAssertionNumber = 0;

            //$this->handleAssertingMode($response, $connection);
        }

            $this->pushContext(self::CONTINUING);
            $this->continuingFromLine = $line;
            $this->send($connection, "step_over -i %d\0");

    }

    /**
     * @param ConnectionInterface $connection
     */
    protected function handlePostmortemMode(ConnectionInterface $connection)
    {
        if (null !== $this->assertionQueue) {
            $assertions = $this->assertionQueue->find(0, 0);
            if (!$assertions->isEmpty()) {
                $this->assertionQueue = $assertions;
                $this->pushContext(self::ASSERTING);
                // @todo fix
                $current = $this->assertionQueue->current();
                /** @var PhpAssertionInterface $current */
                $this->send($connection, $current->getDebuggerCommands());
            } else {
                $this->stop($connection);
            }
        } else {
            $this->stop($connection);
        }
    }

    /**
     * @param \DOMElement $response
     * @param ConnectionInterface $connection
     * @param bool $saveLastRunningResponse
     * @throws \LogicException
     */
    protected function handleAssertingMode(
        \DOMElement $response,
        ConnectionInterface $connection,
        $saveLastRunningResponse = true
    ) {
        /**
         * If we haven't yet sent the first assertion command
         * to the debugger, then we can't assert anything yet.
         * Otherwise, send the response to the current assertion
         * for processing.
         */
        if ($this->currentAssertionNumber > 0) {
            /** @var PhpAssertionInterface $assertion */
            $assertion = $this->assertionQueue->current();

            /**
             * If the assertion has already been processed (non-null isPass()) then we're processing
             * failure commands, and no longer asserting.
             */
            if (null !== $assertion->isPass()) {
                $assertion->handleFailureCommandResponse(new XDebugResponse($this->lastResponse));
            } else {
                $assertion->assert(new XDebugResponse($this->lastResponse), $this->currentAssertionNumber);
            }
        } else if ($saveLastRunningResponse) {
            $this->lastRunningResponse = $response;
        }

        /**
         * Do we have more commands to execute for the current assertion?
         */
        if (!empty($this->commandStack)) {
            $this->currentAssertionNumber++;
            $this->send($connection, array_shift($this->commandStack));
            return;
        }

        if (!isset($assertion)) {
            throw new LogicException('$assertion is not set here. The command stack should not have been empty.');
        }

        /**
         * If the assertion failed, see if there are any additional debugger commands
         * we need to run for it to gather data.
         */
        if (false === $assertion->isPass() &&
            false !== ($commands = $assertion->getFailureCommands()) &&
            !in_array(spl_object_hash($assertion), $this->processedFailures, true)
        ) {
            $this->commandStack = $commands;
            $this->processedFailures[] = spl_object_hash($assertion);
            $this->send($connection, array_shift($this->commandStack));
            return;
        }

        /**
         * Advance to the next assertion. Print output for the assertion
         * we just finalized.
         */
        $this->assertionQueue->next();
        $this->outputAssertionResult($assertion);

        if ($this->assertionQueue->isEmpty()) {
            if ($this->debuggerStopped) {
                $this->stop($connection);
            } else {
                $this->popContext();
                $this->debugOutput('  Returning to last break response.');
                $this->handleRunMode($this->lastRunningResponse, $connection);
                return;
            }
        } else {
            /** @var PhpAssertionInterface $current */
            $current = $this->assertionQueue->current();
            $this->commandStack = $current->getDebuggerCommands();
            $this->currentAssertionNumber = 0;
            $this->debugOutput('   Returning to handleAssertingMode');
            $this->handleAssertingMode($response, $connection, false);
        }
    }

    /**
     * @param $line
     * @param $iteration
     * @return bool|PhpAssertionQueue
     */
    private function getUnprocessedAssertions($line, $iteration)
    {
        if ($assertions = $this->assertions->find($line, $iteration, false)) {
            if (!isset($this->processedAssertions[$line])) {
                $this->processedAssertions[$line] = [];
                $this->processedAssertions[$line][$iteration] = true;
                return $assertions;
            } else if (!isset($this->processedAssertions[$line][$iteration])) {
                $this->processedAssertions[$line][$iteration] = true;
                return $assertions;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param AssertionInterface $assertion
     */
    private function outputAssertionResult(AssertionInterface $assertion)
    {
        switch ($this->output->getVerbosity()) {
            case OutputInterface::VERBOSITY_QUIET:
            case OutputInterface::VERBOSITY_NORMAL:
                return;

            case OutputInterface::VERBOSITY_VERBOSE:
                if ($assertion->isPass()) {
                    $this->output->write('.');
                } else {
                    $this->output->write('E');
                }
                break;

            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                //$this->output->writeln($assertion->getCodeContext());
                if ($assertion->isPass()) {
                    $this->output->writeln(sprintf('%s... PASS', (string) $assertion));
                } else {
                    $this->output->writeln(sprintf('%s... FAIL', (string) $assertion));
                }
                $this->output->writeln('');
                break;

            case OutputInterface::VERBOSITY_DEBUG:
                return;
        }
    }

    /**
     * @param string $text
     */
    private function debugOutput($text)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln(sprintf('<info>%s</info>', $text));
        }
    }

    /**
     * @param \DOMElement $response
     * @throws XdebugException
     */
    private function handleError(\DOMElement $response)
    {
        if ($response->firstChild && $response->firstChild->localName === 'error') {
            throw new XdebugException($response->firstChild->firstChild->nodeValue);
        }
    }

    /**
     * @param ConnectionInterface $connection
     */
    private function stop(ConnectionInterface $connection)
    {
        $this->debugOutput('  Stopping loop');
        $connection->close();
        $this->socket->close();
        $this->loop->stop();
    }

    /**
     * @param $line
     * @return int|mixed
     */
    private function iterationCount($line)
    {
        return !isset($this->iterations[$line]) || null === $this->iterations[$line] ? 0 : $this->iterations[$line];
    }

    /**
     * @param $line
     */
    private function bumpIterationCount($line)
    {
        if (!isset($this->iterations[$line])) {
            $this->iterations[$line] = 1;
        } else {
            $this->iterations[$line]++;
        }
    }

    /**
     * @param $line
     */
    private function removeIteration($line)
    {
        $this->iterations[$line]--;
    }

    /**
     * @param ConnectionInterface $connection
     * @param string|CommandInterface $command
     */
    private function send(ConnectionInterface $connection, $command)
    {
        if ($command instanceof CommandInterface) {
            $command = $command->getXdebugCommand();
        }

        if ($this->output->isDebug()) {
            if (strpos($command, '--') !== false) {
                list($start, $base64) = explode(' -- ', $command);
                $plain = sprintf('%s -- %s', $start, base64_decode($base64));
                $this->output->writeln(sprintf("  %s\n", sprintf($plain, $this->transaction)));
            } else {
                $this->output->writeln(sprintf("  %s\n", sprintf($command, $this->transaction)));
            }
        }
        $connection->write(sprintf($command, $this->transaction++));
    }

    /**
     * @param PhpTestPlan $testPlan
     * @return string
     */
    private function buildRunCommand(PhpTestPlan $testPlan)
    {
        $source = $testPlan->getSubject();

        if (empty($source)) {
            return $this->buildPhpCommand($this->generateCodeCoverage, $testPlan->getFile());
        }

        return $this->buildPhpCommand($this->generateCodeCoverage, null, $source);
    }
}
