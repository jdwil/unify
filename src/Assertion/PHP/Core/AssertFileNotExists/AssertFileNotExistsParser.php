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

namespace JDWil\Unify\Assertion\PHP\Core\AssertFileNotExists;

use JDWil\Unify\Assertion\PHP\AbstractPHPPHPAssertionParser;
use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\PHP\PHPContext;

/**
 * Class AssertFileNotExistsParser
 */
class AssertFileNotExistsParser extends AbstractPHPPHPAssertionParser
{
    /**
     * @var array
     */
    private $files;

    /**
     * @param $comment
     * @param PHPContext $context
     */
    public function initialize($comment, PHPContext $context)
    {
        parent::initialize($comment, $context);

        $this->files = [];
    }

    /**
     * @return AssertionInterface[]|false
     */
    public function parse()
    {
        if (!$this->containsToken([UT_FILE_NOT_EXISTS])) {
            return false;
        }

        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_FILE_PATH:
                    $this->files[] = $token[self::VALUE];
                    break;
            }
        }

        $assertions = [];
        foreach ($this->files as $index => $file) {
            $assertions[] = new AssertFileNotExists(
                $file,
                $this->context->getLine(),
                count($this->files) > 1 ? $index + 1 : 0,
                $this->context->getFile()
            );
        }

        return $assertions;
    }
}
