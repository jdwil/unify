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
     * @param array $comment
     * @param Context $context
     * @return AssertionInterface|false
     */
    public function match($comment, Context $context)
    {
        foreach ($this->getExpressions() as $expression) {
            foreach ($comment as $line) {
                if (preg_match_all($expression, $line, $m, PREG_SET_ORDER)) {
                    foreach ($m as $match) {
                        $files = explode(',', $match[1]);
                        array_walk($files, function (&$path) {
                            $path = trim($path);
                        });
                        return new AssertFileExists($files, $context->getLine(), $context->getFile());
                    }
                }
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
