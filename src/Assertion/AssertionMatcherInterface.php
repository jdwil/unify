<?php

namespace JDWil\Unify\Assertion;

/**
 * Interface AssertionMatcherInterface
 */
interface AssertionMatcherInterface
{
    /**
     * @param array $comment
     * @param Context $context
     * @return AssertionInterface[]
     */
    public function match($comment, Context $context);

    /**
     * @return array
     */
    public function getExpressions();
}
