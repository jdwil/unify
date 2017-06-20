<?php

namespace JDWil\Unify\Assertion\Core\AssertEqual;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\AssertionMatcherInterface;
use JDWil\Unify\Assertion\Context;

/**
 * Class AssertEqualMatcher
 */
class AssertEqualMatcher implements AssertionMatcherInterface
{
    /**
     * @param $comment
     * @param Context $context
     * @return AssertionInterface|false
     */
    public function match($comment, Context $context)
    {
        /**
         * Single value in comments will be asserted as equal, ie:
         *
         * // 'bar'
         * $foo = 'bar';
         */
        if (preg_match('/^[\'\"]?[a-zA-Z0-9_\.]+[\'\"]?$/', $comment, $m)) {
            return new AssertEqual(
                $context->getAssignmentVariable(),
                $m[0],
                $context->getLine(),
                $context->getFile()
            );
        }

        /**
         * Assertions can be formatted as simple expressions:
         *
         * // $foo = 'bar'
         * // $foo == 'bar'
         * // $foo === 'bar'
         */
        if (preg_match('/(\$[a-zA-Z]\w*)\s*(is|==?=?)\s*([\'\"]?[a-zA-Z0-9_:>-]*[\'\"]?)/', $comment, $m)) {
            return new AssertEqual(
                $m[1],
                $m[3],
                $context->getLine(),
                $context->getFile()
            );
        }

        return false;
    }
}
