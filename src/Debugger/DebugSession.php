<?php
declare(strict_types=1);

namespace JDWil\Unify\Debugger;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Server;

class DebugSession
{
    private static $FLAGS = [
        'xdebug.remote_connect_back' => '1',
        'xdebug.profiler_enable' => '1',
        'xdebug.remote_enable' => '1',
        'xdebug.idekey' => 'unify',
        'xdebug.remote_autostart' => 'true',
    ];

    private $loop;
    private $host;
    private $port;
    private $transaction;
    private $debuggerStopped;

    /**
     * @var DebugStep[]
     */
    private $stack;

    /**
     * @var DebugPlan
     */
    private $debugPlan;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->transaction = 1;
        $this->debuggerStopped = false;
        $this->stack = [];
        $this->loop = Factory::create();
    }

    public function debugFile(string $filePath, array $assertions)
    {
        $this->debugPlan = new DebugPlan($assertions);

        $socket = new Server(sprintf('%s:%d', $this->host, $this->port), $this->loop);
        $socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                list ($length, $xml) = explode("\0", $data);
                echo $xml . "\n";
                $this->debug(simplexml_load_string($xml), $connection);
            });
        });

        $this->loop->addTimer(0.1, function () use ($filePath) {
            $command = $this->buildRunCommand($filePath);
            if (0 === pcntl_fork()) {
                `$command`;
                exit(0);
            }
        });

        $this->loop->run();

        return $this->debugPlan;
    }

    public function debugCode(string $code)
    {

    }

    private function debug(\SimpleXMLElement $response, ConnectionInterface $connection)
    {
        if ((string) $response->attributes()->status === 'stopping') {
            $this->debuggerStopped = true;
        }

        if (!empty($this->stack)) {
            $step = array_pop($this->stack);

            switch ($step->getType()) {
                case DebugStep::TYPE_GET_VALUE:
                    foreach ($response->children() as $child) {
                        if ($child->attributes()->name == $step->getVariable()) {
                            $step->setValue((string) $child);
                        }
                    }
                    break;
            }
        }

        do {
            $step = $this->debugPlan->nextStep();
        } while ($step && $this->debuggerStopped && $step->getType() === DebugStep::TYPE_COMMAND);

        if (!$step) {
            $connection->close();
            $this->loop->stop();
            return;
        }

        switch ($step->getType()) {
            case DebugStep::TYPE_COMMAND:
                echo "  " . sprintf($step->getCommand(), $this->transaction) . "\n";
                $connection->write(sprintf($step->getCommand(), $this->transaction++));
                break;

            case DebugStep::TYPE_GET_VALUE:
                $this->stack[] = $step;
                echo sprintf("  context_get -i %d -d 0 -c 0\n", $this->transaction);
                $connection->write(sprintf("context_get -i %d -d 0 -c 0\0", $this->transaction++));
                break;
        }

        if ($this->debugPlan->isComplete()) {
            echo "DONE!!!\n";
            $connection->close();
            $this->loop->stop();
        }
    }

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
