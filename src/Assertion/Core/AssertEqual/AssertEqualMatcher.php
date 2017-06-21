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
    const SINGLE_VALUE = 0;
    const EXPRESSION = 1;

    /**
     * @param array $comment
     * @param Context $context
     * @return AssertionInterface[]
     */
    public function match($comment, Context $context)
    {
        $assertions = [];

        foreach ($this->getExpressions() as $type => $expression) {
            foreach ($comment as $line) {
                if (preg_match_all($expression, $line, $m)) {
                    // @todo stopped here. $m doesn't match up with this loop.
                    foreach ($m as $match) {
                        $variable = $value = '';

                        switch ($type) {
                            case self::SINGLE_VALUE:
                                $variable = $context->getAssignmentVariable();
                                $value = $match[0];
                                break;

                            case self::EXPRESSION:
                                $variable = $match[1];
                                $value = $match[3];
                                break;
                        }

                        $assertions[] = new AssertEqual($variable, $value, $context->getLine(), $context->getFile());
                    }
                }
            }
        }

        return $assertions;
    }

    /**
     * @return array
     */
    public function getExpressions()
    {
        return [
            self::SINGLE_VALUE  => '/^[\'\"]?[a-zA-Z0-9_\.]+[\'\"]?$/',
            self::EXPRESSION    => '/(\$[a-zA-Z]\w*)\s*(is|==?=?)\s*([\'\"]?[a-zA-Z0-9_:>-]*[\'\"]?)/'
        ];
    }
}
