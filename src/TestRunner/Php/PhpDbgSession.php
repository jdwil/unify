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

use JDWil\Unify\TestRunner\TestPlanInterface;
use React\ChildProcess\Process;
use React\Stream\WritableResourceStream;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PHPDBGSession
 */
class PhpDbgSession extends AbstractSession
{
    /**
     * @var PhpTestPlan
     */
    protected $testPlan;

    /**
     * @var WritableResourceStream
     */
    protected $inputStream;

    /**
     * PHPDBGSession constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        parent::__construct($output);
    }

    /**
     * @param TestPlanInterface $testPlan
     * @param bool $verbose
     */
    public function execute(TestPlanInterface $testPlan, $verbose = false)
    {
        $this->testPlan = $testPlan;

        $process = new Process($this->buildCommand($this->testPlan, $verbose));
        $process->start($this->loop);
        $this->inputStream = $process->stdin;

        $process->stdout->on('data', function ($chunk) {
            $this->debug($chunk);
            $this->handleResponse($chunk);
        });
    }

    private function handleResponse($response)
    {

    }

    /**
     * @param $command
     */
    private function write($command)
    {
        $this->debug($command);
        $this->inputStream->write(sprintf("%s\n", $command));
    }

    /**
     * @param string $message
     */
    private function debug($message)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln($message);
        }
    }

    /**
     * @param PhpTestPlan $testPlan
     * @param bool $verbose
     * @return string
     */
    private function buildCommand(PhpTestPlan $testPlan, $verbose)
    {
        $command = $verbose ? 'phpdbg -v' : 'phpdbg';

        if ($code = $testPlan->getSubject()) {
            $command = sprintf('%s -s -e', $command);
        } else {
            $command = sprintf('%s -e %s', $command, $testPlan->getFile());
        }

        return $command;
    }
}
