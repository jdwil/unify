<?php

namespace JDWil\Unify\TestRunner;

use JDWil\Unify\Assertion\AssertionQueue;

/**
 * Class TestPlan
 */
class TestPlan
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var AssertionQueue
     */
    private $assertionQueue;

    /**
     * TestPlan constructor.
     * @param string $file
     * @param AssertionQueue $assertionQueue
     */
    public function __construct($file, AssertionQueue $assertionQueue)
    {
        $this->file = $file;
        $this->assertionQueue = $assertionQueue;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return AssertionQueue
     */
    public function getAssertionQueue()
    {
        return $this->assertionQueue;
    }
}
