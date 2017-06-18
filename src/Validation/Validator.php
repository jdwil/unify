<?php
declare(strict_types=1);

namespace JDWil\Unify\Validation;

use JDWil\Unify\TestRunner\Assertion;
use JDWil\Unify\Trace\Trace;

class Validator
{
    /**
     * @param Trace $trace
     * @param Assertion[] $assertions
     */
    public function validateTrace(Trace $trace, array $assertions)
    {
        foreach ($assertions as $assertion) {
            switch ($assertion->getType()) {
                case Assertion::TYPE_EQUALS:
                    $assignment = $trace->findAssignment($assertion->getLeft(), $assertion->getLine());
                    if ($assignment->getValue() != $assertion->getRight()) {
                        throw new \Exception(sprintf('Assertion error! %s != %s', $assignment->getValue(), $assertion->getRight()));
                    }
                    break;
            }
        }
    }
}
