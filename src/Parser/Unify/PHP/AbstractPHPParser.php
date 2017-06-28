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

use JDWil\Unify\Parser\Unify\PHP\PHPContext;

/**
 * Class AbstractAssertionParser
 */
abstract class AbstractPHPParser implements PHPParserInterface
{
    const TYPE = 0;
    const LINE = 1;
    const VALUE = 2;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var PHPContext
     */
    protected $context;

    /**
     * @param array $tokens
     * @param PHPContext $context
     */
    public function initialize($tokens, PHPContext $context)
    {
        $this->tokens = $tokens;
        $this->context = $context;
        $this->index = -1;
    }

    /**
     * @return bool
     */
    protected function next()
    {
        if (!isset($this->tokens[$this->index + 1])) {
            return false;
        }

        return $this->tokens[++$this->index];
    }

    /**
     * @param int $lookahead
     * @return bool
     */
    protected function peek($lookahead = 1)
    {
        if (!isset($this->tokens[$this->index + $lookahead])) {
            return false;
        }

        return $this->tokens[$this->index + $lookahead];
    }

    protected function reset()
    {
        $this->index = -1;
    }

    /**
     * @param $tokenTypes
     * @return bool
     */
    protected function containsToken($tokenTypes)
    {
        $start = $this->index;
        $this->reset();

        while ($token = $this->next()) {
            if (in_array($token[0], $tokenTypes, true)) {
                $this->index = $start;
                return true;
            }
        }

        $this->index = $start;

        return false;
    }
}
