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
     * @param array $comment
     * @param Context $context
     * @return bool|false|AssertionInterface
     */
    public function handleComment($comment, Context $context)
    {
        foreach ($this->matchers as $matcher) {
            if ($assertion = $matcher->match($comment, $context)) {
                return $assertion;
            }
        }

        return false;
    }
}
