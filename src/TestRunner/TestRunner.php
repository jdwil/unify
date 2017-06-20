<?php

namespace JDWil\Unify\TestRunner;

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
     * TestRunner constructor.
     */
    public function __construct()
    {
        $this->testPlans = [];
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
}
