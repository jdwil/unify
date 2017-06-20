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
     * AssertionQueue constructor.
     */
    public function __construct()
    {
        $this->assertions = [];
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->assertions);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->assertions;
    }

    /**
     * @param AssertionInterface $assertion
     */
    public function add(AssertionInterface $assertion)
    {
        $this->assertions[] = $assertion;
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
     * @return AssertionQueue
     */
    public function findByLine($line)
    {
        $ret = new AssertionQueue();
        foreach ($this->assertions as $assertion) {
            if ($assertion->getLine() === $line) {
                $ret->add($assertion);
            }
        }

        return $ret;
    }
}
