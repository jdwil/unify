<?php
/**
 * Copyright (c) 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details. You should have received a copy of the GNU Lesser General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\TestRunner\Command\Debugger;

use JDWil\Unify\TestRunner\Command\AbstractCommand;

/**
 * Class Subject
 */
class Subject extends AbstractCommand
{
    const EQUALS = '==';
    const STRICT_EQUALS = '===';
    const NOT_EQUALS = '!=';
    const STRICT_NOT_EQUALS = '!==';
    const LESS_THAN = '<';
    const LESS_THAN_OR_EQUAL = '<=';
    const GREATER_THAN = '>';
    const GREATER_THAN_OR_EQUAL = '>=';
    const CONTAINS = 'contains';
    const IS_EMPTY = 'empty';
    const CONTAINS_ONLY = 'contains_only';

    /**
     * @var string
     */
    private $subject;

    /**
     * @var int
     */
    private $comparisonType;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $doTrim;

    private function __construct() {}

    /**
     * @param string $subject
     * @return Subject
     */
    public static function named($subject)
    {
        $ret = new Subject();
        $ret->subject = $subject;

        return $ret;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function equals($value)
    {
        $this->comparisonType = self::EQUALS;
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function strictlyEquals($value)
    {
        $this->comparisonType = self::STRICT_EQUALS;
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function doesNotEqual($value)
    {
        $this->comparisonType = self::NOT_EQUALS;
        $this->value = $value;

        return $this;
    }

    public function strictlyDoesNotEqual($value)
    {
        $this->comparisonType = self::STRICT_NOT_EQUALS;
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function isLessThan($value)
    {
        $this->comparisonType = self::LESS_THAN;
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function isLessThanOrEqualTo($value)
    {
        $this->comparisonType = self::LESS_THAN_OR_EQUAL;
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function isGreaterThan($value)
    {
        $this->comparisonType = self::GREATER_THAN;
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function isGreaterThanOrEqualTo($value)
    {
        $this->comparisonType = self::GREATER_THAN_OR_EQUAL;
        $this->value = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function contains($value)
    {
        $this->comparisonType = self::CONTAINS;
        $this->value = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function isEmpty()
    {
        $this->comparisonType = self::IS_EMPTY;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function containsOnly($value)
    {
        $this->comparisonType = self::CONTAINS_ONLY;
        $this->value = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function trimmed()
    {
        $this->doTrim = true;

        return $this;
    }

    /**
     * @return string
     */
    public function getXdebugCommand()
    {
        return sprintf(
            "eval -i %%d -- %s\0",
            base64_encode($this->getEvalStatement())
        );
    }

    /**
     * @return string
     */
    public function getDbgCommand()
    {
        return sprintf('ev %s', $this->getEvalStatement());
    }

    /**
     * @return string
     */
    private function getEvalStatement()
    {
        switch ($this->comparisonType) {
            case self::CONTAINS:
                return sprintf('in_array(%s, (%s))', (string) $this->value, $this->subject);

            case self::IS_EMPTY:
                return sprintf('empty((%s))', $this->subject);

            case self::CONTAINS_ONLY:
                return sprintf('(%s) === array_filter((%s), %s)', $this->subject, $this->subject, $this->buildFilterClosure());

            default:
                $comparison = $this->doTrim ? 'trim(%s)' : '%s';
                return sprintf(
                    '(' . $comparison . ') %s (%s)',
                    $this->subject,
                    $this->comparisonType,
                    (string) $this->value
                );
        }
    }

    /**
     * @return string
     */
    private function buildFilterClosure()
    {
        switch ($this->value) {
            case 'int':
            case 'ints':
            case 'integer':
            case 'integers':
                return 'is_int';

            case 'string':
            case 'strings':
                return 'is_string';

            case 'float':
            case 'double':
            case 'floats':
            case 'doubles':
                return 'is_float';

            case 'numbers':
                return 'is_numeric';

            case 'arrays':
                return 'is_array';

            default:
                return sprintf('function ($x) { return $x instanceof %s; }', $this->value);
        }
    }
}
