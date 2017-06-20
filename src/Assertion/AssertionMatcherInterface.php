<?php

namespace JDWil\Unify\Assertion;

/**
 * Interface AssertionMatcherInterface
 */
interface AssertionMatcherInterface
{
    /**
     * @param $comment
     * @param Context $context
     * @return AssertionInterface|false
     */
    public function match($comment, Context $context);
}
