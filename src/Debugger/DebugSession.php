<?php

namespace JDWil\Unify\Debugger;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\AssertionQueue;
use JDWil\Unify\Exception\XdebugException;
use JDWil\Unify\TestRunner\TestPlan;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class DebugSession
 */
class DebugSession
{
    const MODE_INITIALIZE = 0;
    const MODE_RUNNING = 1;
    const MODE_ASSERTING = 2;
    const MODE_POSTMORTEM = 3;
    const MODE_STOPPED = 4;

    private static $FLAGS = [
        'xdebug.remote_connect_back' => '1',
        'xdebug.profiler_enable' => '1',
        'xdebug.remote_enable' => '1',
        'xdebug.idekey' => 'unify',
        'xdebug.remote_autostart' => 'true',
    ];

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var LoopInterface
     */
    private $loop;

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
     * @var int
     */
    private $mode;

    /**
     * @var array
     */
    private $initializationCommands;

    /**
     * @var AssertionQueue
     */
    private $assertions;

    /**
     * @var AssertionQueue
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
     * DebugSession constructor.
     * @param string $host
     * @param int $port
     * @param OutputInterface $output
     * @internal param bool $debugOutput
     */
    public function __construct($host, $port, OutputInterface $output)
    {
        $this->host = $host;
        $this->port = $port;
        $this->output = $output;
        $this->transaction = 1;
        $this->debuggerStopped = false;
        $this->loop = Factory::create();
        $this->mode = self::MODE_INITIALIZE;

        $this->initializationCommands = [
            "feature_set -i %d -n show_hidden -v 1\0",
            "feature_set -i %d -n max_children -v 100\0",
            //"feature_set -i %d -n extended_properties -v 1\0",
            "step_into -i %d\0",
        ];
    }

    /**
     * @param TestPlan $testPlan
     * @return AssertionQueue
     * @throws \Exception
     */
    public function debugPhp(TestPlan $testPlan)
    {
        $this->assertions = $testPlan->getAssertionQueue();

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

        $this->socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                list ($length, $xml) = explode("\0", $data);
                if ($this->output->isDebug()) {
                    $this->output->writeln($xml);
                }
                $document = new \DOMDocument();
                $document->loadXML($xml);
                $this->debug($document->documentElement, $connection);
            });
        });

        $this->loop->addTimer(0.001, function () use ($testPlan) {
            $command = $this->buildRunCommand($testPlan);
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
     */
    private function debug(\DOMElement $response, ConnectionInterface $connection)
    {
        $this->handleError($response);

        if ($response->getAttribute('status') === 'stopping') {
            $this->debuggerStopped = true;
            $this->mode = self::MODE_POSTMORTEM;
        }

        switch ($this->mode) {
            case self::MODE_INITIALIZE:
                $command = array_shift($this->initializationCommands);
                if (empty($this->initializationCommands)) {
                    $this->mode = self::MODE_RUNNING;
                }

                $this->send($connection, $command);
                break;

            case self::MODE_RUNNING:
                $line = (int) $response->firstChild->getAttribute('lineno');
                $assertions = $this->assertions->findByLine($line);
                if (!$assertions->isEmpty()) {
                    $this->assertionQueue = $assertions;
                    $this->mode = self::MODE_ASSERTING;
                    $this->commandStack = $this->assertionQueue->current()->getDebuggerCommands();
                    $this->currentAssertionNumber = 1;
                    $this->send($connection, array_shift($this->commandStack));
                }

                if ($this->mode === self::MODE_RUNNING) {
                    $this->send($connection, "step_over -i %d\0");
                }
                break;

            case self::MODE_ASSERTING:
                $assertion = $this->assertionQueue->current();
                $assertion->assert($response, $this->currentAssertionNumber);

                if (!empty($this->commandStack)) {
                    $this->currentAssertionNumber++;
                    $this->send($connection, array_shift($this->commandStack));
                    break;
                } else {
                    $this->assertionQueue->next();
                }

                $this->outputAssertionResult($assertion);

                if ($this->assertionQueue->isEmpty()) {
                    if ($this->debuggerStopped) {
                        $this->stop($connection);
                    } else {
                        $this->mode = self::MODE_RUNNING;
                        $this->send($connection, "step_over -i %d\0");
                    }
                } else {
                    $this->send($connection, $this->assertionQueue->current()->getDebuggerCommands());
                }
                break;

            case self::MODE_POSTMORTEM:
                if (null !== $this->assertionQueue) {
                    $assertions = $this->assertionQueue->findByLine(0);
                    if (!$assertions->isEmpty()) {
                        $this->assertionQueue = $assertions;
                        $this->mode = self::MODE_ASSERTING;
                        $this->send($connection, $this->assertionQueue->current()->getDebuggerCommands());
                    } else {
                        $this->stop($connection);
                    }
                } else {
                    $this->stop($connection);
                }
                break;

            case self::MODE_STOPPED:
                $this->stop($connection);
                break;
        }
    }

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
                $this->output->writeln($assertion->getCodeContext());
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
        $connection->close();
        $this->socket->close();
        $this->loop->stop();
    }

    /**
     * @param ConnectionInterface $connection
     * @param $command
     */
    private function send(ConnectionInterface $connection, $command)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln(sprintf("  %s\n", sprintf($command, $this->transaction)));
        }
        $connection->write(sprintf($command, $this->transaction++));
    }

    /**
     * @param TestPlan $testPlan
     * @return string
     */
    private function buildRunCommand(TestPlan $testPlan)
    {
        if (null === $testPlan->getSource()) {
            $command = 'php';
            foreach (self::$FLAGS as $flag => $value) {
                $command = sprintf('%s -d %s=%s', $command, $flag, $value);
            }

            $command = sprintf('%s -d xdebug.remote_host="%s"', $command, $this->host);
            $command = sprintf('%s -d xdebug.remote_port=%d', $command, $this->port);
            $command = sprintf('%s %s &', $command, $testPlan->getFile());
        } else {
            $command = sprintf('echo %s | php', escapeshellarg($testPlan->getSource()));
            foreach (self::$FLAGS as $flag => $value) {
                $command = sprintf('%s -d %s=%s', $command, $flag, $value);
            }

            $command = sprintf('%s -d xdebug.remote_host="%s"', $command, $this->host);
            $command = sprintf('%s -d xdebug.remote_port=%d', $command, $this->port);
            $command = sprintf('%s &', $command);
        }

        return $command;
    }
}
