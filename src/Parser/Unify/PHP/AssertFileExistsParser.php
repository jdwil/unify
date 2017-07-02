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

use JDWil\Unify\Assertion\PHP\Core\AssertFileExists;
use JDWil\Unify\Assertion\AssertionInterface;

/**
 * Class AssertFileExistsParser
 */
class AssertFileExistsParser extends AbstractPHPParser
{
    /**
     * @return AssertionInterface[]|false
     */
    public function parse()
    {
        if (!$this->containsToken($this->getValidTokens())) {
            return false;
        }

        $files = [];

        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_FILE_PATH:
                    $files[] = $token[self::VALUE];
                    break;
            }
        }

        $assertions = [];
        foreach ($files as $index => $file) {
            if (!in_array($file[0], ['"', "'"], true)) {
                $file = sprintf("'%s'", $file);
            }

            $assertions[] = $this->newAssertion($file, count($files) > 1 ? $index + 1 : 0);
        }

        return $assertions;
    }

    /**
     * @return array
     */
    protected function getValidTokens()
    {
        return [UT_FILE_EXISTS];
    }

    /**
     * @param string $file
     * @param int $iteration
     * @return AssertFileExists
     */
    protected function newAssertion($file, $iteration = 0)
    {
        return new AssertFileExists($file, $iteration);
    }

}
