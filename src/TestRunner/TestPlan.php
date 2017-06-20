<?php

namespace JDWil\Unify\TestRunner;

use JDWil\Unify\Assertion\AssertionInterface;
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
     * @var string
     */
    private $source;

    /**
     * @var AssertionQueue
     */
    private $assertionQueue;

    /**
     * @var AssertionInterface[]
     */
    private $assertions;

    /**
     * TestPlan constructor.
     * @param string $file
     * @param AssertionQueue $assertionQueue
     * @param string $source
     */
    public function __construct($file, AssertionQueue $assertionQueue, $source = null)
    {
        $this->file = $file;
        $this->source = $source;
        $this->assertionQueue = $assertionQueue;
        $this->assertions = [];
        foreach ($assertionQueue->all() as $assertion) {
            $this->assertions[] = $assertion;
        }
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return null|string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return AssertionQueue
     */
    public function getAssertionQueue()
    {
        return $this->assertionQueue;
    }

    /**
     * @return AssertionInterface[]
     */
    public function getAssertions()
    {
        return $this->assertions;
    }

    /**
     * @return bool
     */
    public function isPass()
    {
        foreach ($this->assertions as $assertion) {
            if (!$assertion->isPass()) {
                return false;
            }
        }

       return true;
    }
}
