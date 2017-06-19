<?php
declare(strict_types=1);

namespace JDWil\Unify\Debugger;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\AssertionQueue;
use JDWil\Unify\Exception\XdebugException;
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
     * DebugSession constructor.
     * @param string $host
     * @param int $port
     * @param OutputInterface $output
     * @internal param bool $debugOutput
     */
    public function __construct(string $host, int $port, OutputInterface $output)
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
     * @param string $filePath
     * @param AssertionQueue $assertions
     * @return AssertionQueue
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \JDWil\Unify\Exception\XdebugException
     */
    public function debugFile(string $filePath, AssertionQueue $assertions)
    {
        $this->assertions = $assertions;

        $socket = new Server(sprintf('%s:%d', $this->host, $this->port), $this->loop);
        $socket->on('connection', function (ConnectionInterface $connection) {
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

        $this->loop->addTimer(0.001, function () use ($filePath) {
            $command = $this->buildRunCommand($filePath);
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
                if (!$assertions->empty()) {
                    $this->assertionQueue = $assertions;
                    $this->mode = self::MODE_ASSERTING;
                    $this->send($connection, $this->assertionQueue->current()->getDebuggerCommand());
                }

                if ($this->mode === self::MODE_RUNNING) {
                    $this->send($connection, "step_over -i %d\0");
                }
                break;

            case self::MODE_ASSERTING:
                $assertion = $this->assertionQueue->next();
                $assertion->assert($response);

                if ($this->assertionQueue->empty()) {
                    if ($this->debuggerStopped) {
                        $this->stop($connection);
                    } else {
                        $this->mode = self::MODE_RUNNING;
                        $this->send($connection, "step_over -i %d\0");
                    }
                } else {
                    $this->send($connection, $this->assertionQueue->current()->getDebuggerCommand());
                }
                break;

            case self::MODE_POSTMORTEM:
                $assertions = $this->assertionQueue->findByLine(0);
                if (!$assertions->empty()) {
                    $this->assertionQueue = $assertions;
                    $this->mode = self::MODE_ASSERTING;
                    $this->send($connection, $this->assertionQueue->current()->getDebuggerCommand());
                } else {
                    $this->stop($connection);
                }
                break;

            case self::MODE_STOPPED:
                $this->stop($connection);
                break;
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
     * @param string $filePath
     * @return string
     */
    private function buildRunCommand(string $filePath)
    {
        $command = 'php';
        foreach (self::$FLAGS as $flag => $value) {
            $command = sprintf('%s -d %s=%s', $command, $flag, $value);
        }

        $command = sprintf('%s -d xdebug.remote_host="%s"', $command, $this->host);
        $command = sprintf('%s -d xdebug.remote_port=%d', $command, $this->port);
        $command = sprintf('%s %s &', $command, $filePath);

        return $command;
    }
}
