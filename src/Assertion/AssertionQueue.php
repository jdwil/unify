<?php

namespace JDWil\Unify\Assertion;

/**
 * Class AssertionQueue
 */
class AssertionQueue
{
    /**
     * @var AssertionInterface[]
     */
    private $assertions;

    /**
     * @var AssertionInterface[]
     */
    private $cache;

    /**
     * AssertionQueue constructor.
     */
    public function __construct()
    {
        $this->assertions = [];
        $this->cache = [];
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->assertions);
    }

    /**
     * @return AssertionInterface[]
     */
    public function getQueue()
    {
        return $this->assertions;
    }

    /**
     * @return AssertionInterface[]
     */
    public function all()
    {
        return $this->cache;
    }

    /**
     * @param AssertionInterface $assertion
     */
    public function add(AssertionInterface $assertion)
    {
        $this->assertions[] = $assertion;
        $this->cache[] = $assertion;
    }

    /**
     * @return AssertionInterface|null
     */
    public function current()
    {
        return isset($this->assertions[0]) ? $this->assertions[0] : null;
    }

    /**
     * @return AssertionInterface
     */
    public function next()
    {
        return array_shift($this->assertions);
    }

    /**
     * @param int $line
     * @param int $iteration
     * @param bool $cloneIfRun
     * @return AssertionQueue
     */
    public function find($line, $iteration, $cloneIfRun = true)
    {
        $ret = new AssertionQueue();
        foreach ($this->assertions as $assertion) {
            if ($assertion->getLine() === $line &&
                ($assertion->getIteration() === $iteration ||
                 $assertion->getIteration() === 0
                )
            ) {
                if ($cloneIfRun && null !== $assertion->isPass()) {
                    $newAssertion = clone $assertion;
                    $newAssertion->setIteration($iteration);
                    $this->cache[] = $newAssertion;
                    $ret->add($newAssertion);
                } else {
                    $ret->add($assertion);
                }
            }
        }

        return $ret;
    }
}
