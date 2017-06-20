<?php

namespace JDWil\Unify\Assertion;

/**
 * Class Pipeline
 */
class Pipeline
{
    /**
     * @var AssertionMatcherInterface[]
     */
    private $matchers;

    /**
     * Pipeline constructor.
     */
    public function __construct()
    {
        $this->matchers = [];
    }

    /**
     * @param AssertionMatcherInterface $matcher
     */
    public function addMatcher(AssertionMatcherInterface $matcher)
    {
        $this->matchers[] = $matcher;
    }

    /**
     * @param $line
     * @param Context $context
     * @return bool|false|AssertionInterface
     */
    public function handleLine($line, Context $context)
    {
        foreach ($this->matchers as $matcher) {
            if ($assertion = $matcher->match($line, $context)) {
                return $assertion;
            }
        }

        return false;
    }
}
