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
        foreach ($this->getExpressions() as $expression) {
            if (preg_match($expression, $comment, $m)) {
                $files = explode(',', $m[1]);
                array_walk($files, function (&$file) {
                    $file = trim($file);
                });
                return new AssertFileNotExists($files, $context->getLine(), $context->getFile());
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
            '/deletes?\sf?i?l?e?s?\s?(.*)/i'
        ];
    }
}
