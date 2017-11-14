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
use JDWil\Unify\Assertion\Php\Core\AssertArrayHasKey;

class AssertArrayHasKeyParser extends AbstractPhpParser
{
    /**
     * @return false|AssertionInterface[]
     */
    public function parse()
    {
        if (!$this->containsToken([UT_ARRAY_CONTAINS_KEY])) {
            return false;
        }

        $variable = $key = null;

        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_VARIABLE:
                    $variable = $token[self::VALUE];
                    break;

                case UT_QUOTED_STRING:
                case UT_INTEGER:
                case UT_FLOAT:
                    $key = $token[self::VALUE];
                    break;
            }
        }

        if (null === $variable) {
            $variable = $this->context->getCodeContext();
        }

        if ($iterations = $this->getIterations()) {
            $ret = [];
            foreach ($iterations as $iteration) {
                $ret[] = new AssertArrayHasKey(
                    $variable,
                    $key,
                    $iteration
                );
            }

            return $ret;
        }

        return [
            new AssertArrayHasKey($variable, $key)
        ];
    }
}
