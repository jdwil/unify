<?php

namespace JDWil\Unify\Assertion\Core\AssertEqual;

use JDWil\Unify\Assertion\AbstractAssertionParser;
use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\Context;

/**
 * Class AssertEqualMatcher
 */
class AssertEqualParser extends AbstractAssertionParser
{
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';

    /**
     * @var string
     */
    protected $variable;

    /**
     * @var array
     */
    protected $valueTypes;

    /**
     * @var array
     */
    protected $values;

    /**
     * @param $comment
     * @param Context $context
     */
    public function initialize($comment, Context $context)
    {
        parent::initialize($comment, $context);

        $this->values = [];
        $this->valueTypes = [];
        $this->variable = null;
    }

    /**
     * @return AssertionInterface[]|false
     */
    public function parse()
    {
        if (!$this->containsToken([UT_EQUALS, UT_EQUALS_MATCH_TYPE])) {
            return false;
        }

        $assertions = [];

        while ($token = $this->next()) {
            // @todo add arrays, plus more I'm sure.
            switch ($token[self::TYPE]) {
                case UT_VARIABLE:
                    $this->variable = $token[self::VALUE];
                    break;

                case UT_QUOTED_STRING:
                    $this->valueTypes[] = self::TYPE_STRING;
                    $this->values[] = $token[self::VALUE];
                    break;

                case UT_FLOAT:
                    $this->valueTypes[] = self::TYPE_FLOAT;
                    $this->values[] = $token[self::VALUE];
                    break;

                case UT_INTEGER:
                    $this->valueTypes[] = self::TYPE_INTEGER;
                    $this->values[] = $token[self::VALUE];
                    break;
            }
        }

        if (count($this->values)) {
            for ($num = count($this->values), $i = 1; $i <= $num; $i++) {
                $assertions[] = new AssertEqual(
                    $this->variable,
                    $this->values[$i - 1],
                    $this->context->getLine(),
                    $num > 1 ? $i : 0,
                    $this->context->getFile()
                );
            }
        }

        return $assertions;
    }
}
