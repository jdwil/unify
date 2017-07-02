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
 * Class Variable
 */
class Variable extends AbstractCommand
{
    const EQUALS = '==';
    const STRICT_EQUALS = '===';
    const NOT_EQUALS = '!=';
    const STRICT_NOT_EQUALS = '!==';
    const LESS_THAN = '<';
    const LESS_THAN_OR_EQUAL = '<=';
    const GREATER_THAN = '>';
    const GREATER_THAN_OR_EQUAL = '>=';

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

    private function __construct() {}

    /**
     * @param string $subject
     * @return Variable
     */
    public static function named($subject)
    {
        $ret = new Variable();
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
        return sprintf('%s %s %s', $this->subject, $this->comparisonType, (string) $this->value);
    }
}
