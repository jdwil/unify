<?php

namespace JDWil\Unify\TestRunner;

use JDWil\Unify\Debugger\DebugSessionFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestRunnerFactory
 */
class TestRunnerFactory
{
    /**
     * @var DebugSessionFactory
     */
    private $debugSessionFactory;

    /**
     * TestRunnerFactory constructor.
     * @param DebugSessionFactory $debugSessionFactory
     */
    public function __construct(DebugSessionFactory $debugSessionFactory)
    {
        $this->debugSessionFactory = $debugSessionFactory;
    }

    /**
     * @param OutputInterface $output
     * @return TestRunner
     */
    public function create(OutputInterface $output)
    {
        return new TestRunner($this->debugSessionFactory, $output);
    }
}