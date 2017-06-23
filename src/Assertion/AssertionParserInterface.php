<?php

namespace JDWil\Unify\Assertion;

/**
 * Interface AssertionMatcherInterface
 */
interface AssertionParserInterface
{
    /**
     * @return false|AssertionInterface[]
     */
    public function parse();

    /**
     * @param $comment
     * @param Context $context
     */
    public function initialize($comment, Context $context);
}
