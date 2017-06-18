<?php
declare(strict_types=1);

namespace JDWil\Unify\TestRunner;

use Symfony\Component\Process\Process;

class TestRunner
{
    private static $FLAGS = [
        'xdebug.trace_output_dir' => '/tmp',
        'xdebug.trace_output_name' => 'unify.trace',
        'xdebug.auto_trace' => 1,
        'xdebug.trace_format' => 0,
        'xdebug.collect_assignments' => 1,
        'xdebug.collect_includes' => 1,
        'xdebug.collect_params' => 4,
        'xdebug.collect_return' => 1,
        'xdebug.collect_vars' => 1
    ];

    public function run(string $filePath, array $assertions)
    {
        $assertionLookup = [];
        /** @var Assertion $assertion */
        foreach ($assertions as $assertion) {
            if (!isset($assertionLookup[$assertion->getLine()])) {
                $assertionLookup[$assertion->getLine()] = [];
            }
            $assertionLookup[$assertion->getLine()][] = $assertion;
        }

        $process = new Process($this->buildCommand($filePath));
        $process->run();

        return '/tmp/unify.trace.xt';
    }

    private function buildCommand(string $filePath)
    {
        $command = 'php';
        foreach (self::$FLAGS as $flag => $value) {
            $command = sprintf('%s -d %s=%s', $command, $flag, (string) $value);
        }

        $command = sprintf('%s %s', $command, $filePath);

        return $command;
    }
}
