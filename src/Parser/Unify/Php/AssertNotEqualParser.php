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

namespace JDWil\Unify\Parser\Unify\Php;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\Php\Core\AssertNotEqual;
use JDWil\Unify\Assertion\Php\Core\AssertStrictNotEqual;

/**
 * Class AssertNotEqualParser
 */
class AssertNotEqualParser extends AbstractComparisonParser
{
    /**
     * @return array
     */
    protected function getValidTokens()
    {
        return [UT_NOT_EQUALS, UT_NOT_EQUALS_MATCH_TYPE];
    }

    /**
     * @param string $variable
     * @param string $value
     * @param int $iteration
     * @return AssertionInterface
     */
    protected function newAssertion($variable, $value, $iteration = 0)
    {
        if ($this->containsToken([UT_NOT_EQUALS_MATCH_TYPE])) {
            return new AssertStrictNotEqual($variable, $value, $iteration);
        }

        return new AssertNotEqual($variable, $value, $iteration);
    }
}
