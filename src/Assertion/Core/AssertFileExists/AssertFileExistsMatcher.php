<?php

namespace JDWil\Unify\Assertion\Core\AssertFileExists;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\AssertionMatcherInterface;
use JDWil\Unify\Assertion\Context;

/**
 * Class AssertFileExistsMatcher
 */
class AssertFileExistsMatcher implements AssertionMatcherInterface
{
    /**
     * @param $comment
     * @param Context $context
     * @return AssertionInterface|false
     */
    public function match($comment, Context $context)
    {
        /**
         * Assert a file exists
         *
         * // creates file /path/to/file.xyz
         */
        if (preg_match('/creates? file (\S+)/i', $comment, $m)) {
            return new AssertFileExists($m[1], $context->getLine(), $context->getFile());
        }

        return false;
    }
}
