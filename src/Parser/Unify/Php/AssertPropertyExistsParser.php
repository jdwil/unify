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
use JDWil\Unify\Assertion\Php\Core\AssertPropertyExists;

/**
 * Class AssertPropertyExistsParser
 */
class AssertPropertyExistsParser extends AbstractPhpParser
{
    /**
     * @return false|AssertionInterface[]
     */
    public function parse()
    {
        if (!$this->containsToken($this->getValidTokens())) {
            return false;
        }

        $classOrVariableName = $propertyName = null;
        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_PROPERTY_REFERENCE:
                case UT_VARIABLE:
                    $classOrVariableName = $token[self::VALUE];
                    break;

                case UT_QUOTED_STRING:
                    $propertyName = $token[self::VALUE];
                    break;
            }
        }

        if (strpos($classOrVariableName, '::') !== false) {
            list($classOrVariableName, $propertyName) = explode('::', $classOrVariableName);
        }

        if (strpos($classOrVariableName, '$') === 1) {
            $classOrVariableName = str_replace(["'", '"'], '', $classOrVariableName);
        }

        if (!in_array($propertyName[0], ['"', "'"], true)) {
            $propertyName = sprintf("'%s'", $propertyName);
        }

        if ($iterations = $this->getIterations()) {
            $ret = [];
            foreach ($iterations as $iteration) {
                $ret[] = $this->newAssertion($classOrVariableName, $propertyName, $iteration);
            }

            return $ret;
        }

        return [
            $this->newAssertion($classOrVariableName, $propertyName)
        ];
    }

    /**
     * @param string $classOrVariableName
     * @param string $propertyName
     * @param int $iteration
     * @return AssertPropertyExists
     */
    protected function newAssertion($classOrVariableName, $propertyName, $iteration = 0)
    {
        return new AssertPropertyExists($classOrVariableName, $propertyName, $iteration);
    }

    /**
     * @return array
     */
    protected function getValidTokens()
    {
        return [UT_OBJECT_HAS_PROPERTY];
    }
}
