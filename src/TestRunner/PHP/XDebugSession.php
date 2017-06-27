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

namespace JDWil\Unify\TestRunner\PHP;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\PHP\PHPAssertionInterface;
use JDWil\Unify\Assertion\PHP\PHPAssertionQueue;
use JDWil\Unify\Exception\XdebugException;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\XdebugResponse;
use JDWil\Unify\TestRunner\PHP\PHPTestPlan;
use JDWil\Unify\TestRunner\TestPlan;
use JDWil\Unify\TestRunner\TestPlanInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class XDebugSession
 */
class XDebugSession extends AbstractSession
{
    private static $FLAGS = [
        'xdebug.remote_connect_back' => '1',
        'xdebug.profiler_enable' => '1',
        'xdebug.remote_enable' => '1',
        'xdebug.idekey' => 'unify',
        'xdebug.remote_autostart' => 'true',
    ];

    /**
     * @var PHPTestPlan
     */
    protected $testPlan;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

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
     * @var PHPAssertionQueue
     */
    private $assertions;

    /**
     * @var PHPAssertionQueue
     */
    private $assertionQueue;

    /**
     * @var Server
     */
    private $socket;

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
     * DebugSession constructor.
     * @param string $host
     * @param int $port
     * @param OutputInterface $output
     * @internal param bool $debugOutput
     */
    public function __construct($host, $port, OutputInterface $output)
    {
        parent::__construct($output);

        $this->mode = [];
        $this->host = $host;
        $this->port = $port;
        $this->transaction = 1;
        $this->debuggerStopped = false;
        $this->iterations = [];
        $this->processedAssertions = [];

        $this->setContext(self::INITIALIZE);

        $this->initializationCommands = [
            "feature_set -i %d -n show_hidden -v 1\0",
            "feature_set -i %d -n max_children -v 100\0",
            "step_into -i %d\0",
            sprintf("eval -i %%d -- %s\0", base64_encode('runkit_function_redefine(\'bar\', function () {
    return 1;
});')),
        ];
    }

    /**
     * @param TestPlanInterface $testPlan
     * @return PHPAssertionQueue
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

        $this->bootSocketServer();

        $this->socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                list ($length, $xml) = explode("\0", $data);
                $this->lastResponse = $xml;
                if ($this->output->isDebug()) {
                    $this->output->writeln($xml);
                }
                $document = new \DOMDocument();
                $document->loadXML($xml);
                $this->debug($document->documentElement, $connection);
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
                /** @var PHPAssertionInterface $current */
                $this->commandStack = $current->getDebuggerCommands();
            } else {
                throw new \LogicException('assertionQueue::current() returned null');
            }
            $this->currentAssertionNumber = 0;
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
                /** @var PHPAssertionInterface $current */
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
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function handleAssertingMode(\DOMElement $response, ConnectionInterface $connection)
    {
        /**
         * If we haven't yet sent the first assertion command
         * to the debugger, then we can't assert anything yet.
         * Otherwise, send the response to the current assertion
         * for processing.
         */
        if ($this->currentAssertionNumber > 0) {
            $assertion = $this->assertionQueue->current();
            $assertion->assert(new XdebugResponse($this->lastResponse), $this->currentAssertionNumber);
        } else {
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
            // @todo fix
            $this->send($connection, $this->assertionQueue->current()->getDebuggerCommands());
        }
    }

    /**
     * @param $line
     * @param $iteration
     * @return bool|PHPAssertionQueue
     */
    private function getUnprocessedAssertions($line, $iteration)
    {
        if ($assertions = $this->assertions->find($line, $iteration)) {
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
        return isset($this->iterations[$line]) ? $this->iterations[$line] : 0;
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
     * @param ConnectionInterface $connection
     * @param string|CommandInterface $command
     */
    private function send(ConnectionInterface $connection, $command)
    {
        if ($command instanceof CommandInterface) {
            $command = $command->getXdebugCommand();
        }

        if ($this->output->isDebug()) {
            $this->output->writeln(sprintf("  %s\n", sprintf($command, $this->transaction)));
        }
        $connection->write(sprintf($command, $this->transaction++));
    }

    /**
     * @param PHPTestPlan $testPlan
     * @return string
     */
    private function buildRunCommand(PHPTestPlan $testPlan)
    {
        if (null === $testPlan->getSubject()) {
            $command = 'php';
            foreach (self::$FLAGS as $flag => $value) {
                $command = sprintf('%s -d %s=%s', $command, $flag, $value);
            }

            $command = sprintf('%s -d xdebug.remote_host="%s"', $command, $this->host);
            $command = sprintf('%s -d xdebug.remote_port=%d', $command, $this->port);
            $command = sprintf('%s %s &', $command, $testPlan->getFile());
        } else {
            $source = $testPlan->getSubject();
            $source .= "\n\nexit(0);";
            $command = sprintf('echo %s | php', escapeshellarg($source));
            foreach (self::$FLAGS as $flag => $value) {
                $command = sprintf('%s -d %s=%s', $command, $flag, $value);
            }

            $command = sprintf('%s -d xdebug.remote_host="%s"', $command, $this->host);
            $command = sprintf('%s -d xdebug.remote_port=%d', $command, $this->port);
            $command = sprintf('%s &', $command);
        }

        return $command;
    }

    private function bootSocketServer()
    {
        $attempts = 100;
        do {
            try {
                $this->socket = new Server(sprintf('%s:%d', $this->host, $this->port), $this->loop);
                $retry = false;
            } catch (\Exception $e) {
                $attempts--;
                if (!$attempts) {
                    throw $e;
                }
                $retry = true;
                usleep(100);
            }
        } while ($retry);
    }
}