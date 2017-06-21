<?php

namespace JDWil\Unify\TestRunner;

use JDWil\Unify\Debugger\DebugSessionFactory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestRunner
 */
class TestRunner
{
    /**
     * @var TestPlan[]
     */
    private $testPlans;

    /**
     * @var DebugSessionFactory
     */
    private $debugSessionFactory;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * TestRunner constructor.
     */
    public function __construct(DebugSessionFactory $debugSessionFactory, OutputInterface $output)
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

        foreach ($this->testPlans as $testPlan) {
            $session = $this->debugSessionFactory->create($this->output);
            $session->debugPhp($testPlan);

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
     * @param TestPlan $testPlan
     */
    public function addTestPlan(TestPlan $testPlan)
    {
        $this->testPlans[] = $testPlan;
    }

    /**
     * @return TestPlan[]
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
        $files = $assertions = $passed = $failed = 0;
        $failures = [];

        foreach ($this->testPlans as $testPlan) {
            $files++;
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
                        'file' => sprintf('%s:%d', $assertion->getFile(), $assertion->getLine()),
                        'assertion' => (string) $assertion
                    ];
                }
            }
        }

        $this->output->writeln(sprintf(' %s', $status));
        foreach ($failures as $failure) {
            $this->output->writeln(sprintf('  Failure in %s', $failure['file']));
            $this->output->writeln(sprintf('    %s failed', $failure['assertion']));
            $this->output->writeln('');
        }

        $this->output->writeln(sprintf(' %d file(s). %d Assertions. %d/%d Passed.', $files, $assertions, $passed, $assertions));
    }
}
