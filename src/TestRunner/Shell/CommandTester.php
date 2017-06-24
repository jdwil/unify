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

namespace JDWil\Unify\TestRunner\Shell;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\Shell\Core\AssertCommandOutputEquals;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class CommandTester
 */
class CommandTester
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * CommandTester constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param ShellTestPlan $testPlan
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    public function test(ShellTestPlan $testPlan)
    {
        $command = $testPlan->getSubject();
        $process = new Process($command);
        $process->run();
        $output = $process->getOutput();

        /** @var AssertCommandOutputEquals $assertion */
        foreach ($testPlan->getAssertions() as $assertion) {
            $assertion->assert($output);
            $this->printTestResult($assertion);
        }
    }

    /**
     * @param AssertionInterface $assertion
     */
    private function printTestResult(AssertionInterface $assertion)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE) {
            if ($assertion->isPass()) {
                $this->output->writeln(sprintf('%s... PASS', (string) $assertion));
            } else {
                $this->output->writeln(sprintf('%s... FAIL', (string) $assertion));
            }
        }
    }
}
