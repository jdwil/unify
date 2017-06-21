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
        foreach ($this->getExpressions() as $expression) {
            if (preg_match($expression, $comment, $m)) {
                $files = explode(',', $m[1]);
                array_walk($files, function (&$path) {
                    $path = trim($path);
                });
                return new AssertFileExists($files, $context->getLine(), $context->getFile());
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getExpressions()
    {
        return [
            '/creates?\sf?i?l?e?s?\s?(.*)/i'
        ];
    }
}
