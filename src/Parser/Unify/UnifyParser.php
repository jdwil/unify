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

declare(strict_types=1);

namespace JDWil\Unify\Parser\Unify;

use Phlexy\Lexer\Stateful;

class UnifyParser
{
    /**
     * @var Stateful
     */
    private $lexer;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var int
     */
    private $index;

    /**
     * UnifyParser constructor.
     * @param Stateful $lexer
     */
    public function __construct(Stateful $lexer)
    {
        $this->lexer = $lexer;
    }

    public function parse($comment)
    {
        try {
            $this->tokens = $this->lexer->lex($comment);
            $this->index = -1;
        } catch (\Exception $e) {
            /* This comment isn't valid Unify code */
            return false;
        }

        $tokenGroups = $tokenGroup = [];
        while ($token = $this->skipWhitespace()) {
            if ($token[0] === UT_END_ASSERTION) {
                $tokenGroups[] = $tokenGroup;
                $tokenGroup = [];
            } else {
                $tokenGroup[] = $token;
            }
        }

        $tokenGroups[] = $tokenGroup;

        return $tokenGroups;
    }

    private function next()
    {
        if (!isset($this->tokens[$this->index + 1])) {
            return false;
        }

        return $this->tokens[++$this->index];
    }

    private function skipWhitespace()
    {
        if (!$this->next()) {
            return false;
        }

        while (isset($this->tokens[$this->index]) && $this->isWhitespace($this->tokens[$this->index])) {
            $this->index++;
        }

        return isset($this->tokens[$this->index]) ? $this->tokens[$this->index] : false;
    }

    private function isWhitespace($token)
    {
        return $token[0] === UT_WHITESPACE;
    }
}
