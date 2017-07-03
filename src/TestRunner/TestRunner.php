<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\TestRunner;

use JDWil\Unify\Assertion\Unbounded\UnboundedAssertionQueue;
use JDWil\Unify\TestRunner\PHP\XDebugSessionFactory;
use JDWil\Unify\TestRunner\PHP\PHPTestPlan;
use JDWil\Unify\TestRunner\Shell\CommandTester;
use JDWil\Unify\TestRunner\Shell\ShellTestPlan;
use JDWil\Unify\TestRunner\Unbounded\UnboundedTester;
use JDWil\Unify\TestRunner\Unbounded\UnboundedTestPlan;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestRunner
 */
class TestRunner
{
    /**
     * @var TestPlanInterface[]
     */
    private $testPlans;

    /**
     * @var XDebugSessionFactory
     */
    private $debugSessionFactory;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * TestRunner constructor.
     * @param XDebugSessionFactory $debugSessionFactory
     * @param OutputInterface $output
     */
    public function __construct(XDebugSessionFactory $debugSessionFactory, OutputInterface $output)
    {
        $this->testPlans = [];
        $this->debugSessionFactory = $debugSessionFactory;
        $this->output = $output;
    }

    public function execute()
    {
        $showProgress = in_array(
            $this->output->getVerbosity(),
            [
                OutputInterface::VERBOSITY_QUIET,
                OutputInterface::VERBOSITY_NORMAL
            ],
            true
        );

        if ($showProgress) {
            $progress = new ProgressBar($this->output, count($this->testPlans));
            $progress->start();
        }

        foreach ($this->testPlans as $i => $testPlan) {
            if ($testPlan instanceof PHPTestPlan) {
                $this->debug('  Executing PHP test plan');
                $session = $this->debugSessionFactory->create($this->output);
                $session->execute($testPlan);
            } else if ($testPlan instanceof ShellTestPlan) {
                $this->debug('  Executing shell test plan');
                $tester = new CommandTester($this->output);
                $tester->test($testPlan);
            } else if ($testPlan instanceof UnboundedTestPlan) {
                $this->debug('  Executing unbounded test plan');
                $tester = new UnboundedTester($this->testPlans, $i);
                $tester->test($testPlan);
            }

            if ($showProgress && isset($progress)) {
                $progress->advance();
            }
        }

        if ($showProgress && isset($progress)) {
            $progress->finish();
        }

        $this->output->writeln('');
        $this->printResults();
    }

    /**
     * @param TestPlanInterface $testPlan
     */
    public function addTestPlan(TestPlanInterface $testPlan)
    {
        $this->testPlans[] = $testPlan;
    }

    /**
     * @return TestPlanInterface[]
     */
    public function getTestPlans()
    {
        return $this->testPlans;
    }

    /**
     * @return int
     */
    public function statusCode()
    {
        foreach ($this->testPlans as $testPlan) {
            if (!$testPlan->isPass()) {
                return 1;
            }
        }

        return 0;
    }

    private function printResults()
    {
        $this->output->writeln('');

        $status = 'SUCCESS';
        $testPlans = $assertions = $passed = $failed = 0;
        $failures = [];

        foreach ($this->testPlans as $testPlan) {
            $testPlans++;
            if (!$testPlan->isPass()) {
                $status = 'FAILURES';
            }

            foreach ($testPlan->getAssertions() as $assertion) {
                $assertions++;
                if ($assertion->isPass()) {
                    $passed++;
                } else {
                    $failed++;
                    $failures[] = [
                        'file' => sprintf('%s:%s', $assertion->getFile(), $assertion->getLine()),
                        'assertion' => (string) $assertion,
                        'failureMessage' => $assertion->getFailureMessage()
                    ];
                }
            }
        }

        $this->output->writeln(sprintf(' %s', $status));
        foreach ($failures as $failure) {
            $this->output->writeln(sprintf('  Failure in %s', $failure['file']));
            $this->output->writeln(sprintf('    %s failed', $failure['assertion']));
            $this->output->writeln('');
            $this->output->writeln($failure['failureMessage']);
            $this->output->writeln('');
        }

        $this->output->writeln(sprintf(' %d Test Plan(s). %d Assertions. %d/%d Passed.', $testPlans, $assertions, $passed, $assertions));
    }

    protected function debug($message)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln(sprintf('<info>%s</info>', $message));
        }
    }
}
