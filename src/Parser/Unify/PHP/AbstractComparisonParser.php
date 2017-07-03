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

/**
 * Class AbstractComparisonParser
 */
abstract class AbstractComparisonParser extends AbstractPHPParser
{
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';
    const TYPE_CONSTANT = 'constant';

    /**
     * @return AssertionInterface[]|false
     */
    public function parse()
    {
        if (!$this->containsToken($this->getValidTokens())) {
            return false;
        }

        $assertions = $values = $valueTypes = [];
        $variable = null;

        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_VARIABLE:
                    $variable = $token[self::VALUE];
                    break;

                case UT_QUOTED_STRING:
                    $valueTypes[] = self::TYPE_STRING;
                    $values[] = $token[self::VALUE];
                    break;

                case UT_FLOAT:
                    $valueTypes[] = self::TYPE_FLOAT;
                    $values[] = $token[self::VALUE];
                    break;

                case UT_INTEGER:
                    $valueTypes[] = self::TYPE_INTEGER;
                    $values[] = $token[self::VALUE];
                    break;

                case UT_ARRAY:
                    $valueTypes[] = self::TYPE_ARRAY;
                    $values[] = $token[self::VALUE];
                    break;

                case UT_CONSTANT:
                    $valueTypes[] = self::TYPE_CONSTANT;
                    $values[] = $token[self::VALUE];
                    break;
            }
        }

        if (count($values) > 1) {
            for ($num = count($values), $i = 1; $i <= $num; $i++) {
                $assertions[] = $this->newAssertion($variable, $values[$i - 1], $num > 1 ? $i : 0);
            }
        } else if (count($values)) {
            if ($iterations = $this->getIterations()) {
                foreach ($iterations as $iteration) {
                    $assertions[] = $this->newAssertion($variable, $values[0], $iteration);
                }
            } else {
                $assertions[] = $this->newAssertion($variable, $values[0]);
            }
        }

        return $assertions;
    }

    /**
     * @return array
     */
    abstract protected function getValidTokens();

    /**
     * @param string $variable
     * @param string $value
     * @param int $iteration
     * @return AssertionInterface
     */
    abstract protected function newAssertion($variable, $value, $iteration = 0);
}
