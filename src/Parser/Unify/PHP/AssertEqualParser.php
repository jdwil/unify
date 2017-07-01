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

namespace JDWil\Unify\Parser\Unify\PHP;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\PHP\Core\AssertEqual;
use JDWil\Unify\ValueObject\PHPContext;

/**
 * Class AssertEqualParser
 */
class AssertEqualParser extends AbstractPHPParser
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
     * @param PHPContext $context
     */
    public function initialize($comment, PHPContext $context)
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
                    $num > 1 ? $i : 0
                );
            }
        }

        return $assertions;
    }
}
