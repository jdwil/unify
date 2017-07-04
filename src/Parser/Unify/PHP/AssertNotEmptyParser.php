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
use JDWil\Unify\Assertion\PHP\Core\AssertNotEmpty;

class AssertNotEmptyParser extends AbstractPHPParser
{
    /**
     * @return false|AssertionInterface[]
     */
    public function parse()
    {
        if (!$this->containsToken([UT_NOT_EMPTY])) {
            return false;
        }

        $subject = null;

        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_VARIABLE:
                case UT_FUNCTION_CALL:
                case UT_METHOD_CALL:
                    $subject = $token[self::VALUE];
                    break;
            }
        }

        if (null === $subject) {
            return false;
        }

        $ret = [];
        foreach ($this->getIterations() as $iteration) {
            $ret[] = new AssertNotEmpty($subject, $iteration);
        }

        return $ret;
    }
}
