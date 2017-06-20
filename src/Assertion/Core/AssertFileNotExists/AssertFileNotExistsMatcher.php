<?php

namespace JDWil\Unify\Assertion\Core\AssertFileNotExists;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\AssertionMatcherInterface;
use JDWil\Unify\Assertion\Context;

/**
 * Class AssertFileNotExistsMatcher
 */
class AssertFileNotExistsMatcher implements AssertionMatcherInterface
{
    /**
     * @param $comment
     * @param Context $context
     * @return AssertionInterface|false
     */
    public function match($comment, Context $context)
    {
        /**
         * Assert a file does not exist.
         *
         * // deletes file /path/to/file.xyz
         */
        if (preg_match('/deletes? file (\S+)/i', $comment, $m)) {
            return new AssertFileNotExists($m[1], $context->getLine(), $context->getFile());
        }

        return false;
    }
}
