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

namespace JDWil\Unify\Parser;

use JDWil\Unify\ValueObject\LineRange;

class PhpTokenizer
{
    /**
     * @var array
     */
    private $lines;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var int
     */
    private $index;

    public function __construct($code)
    {
        $this->lines = explode("\n", $code);
        array_unshift($this->lines, '');
        $this->tokens = token_get_all($code);
        array_walk($this->lines, function (&$line) {
            $line = sprintf("%s\n", $line);
        });
        $this->index = 0;
    }

    /**
     * @param $line
     * @return LineRange
     */
    public function toLineRange($line)
    {
        $tmp = $this->index;
        $this->seekLine($line);

        $currentLine = $this->seekStartOfCodeBlock($line);

        $start = $currentLine;

        while ($token = $this->next()) {
            if ($token === ';') {
                break;
            }

            if (is_array($token)) {
                $currentLine = $token[2];
                if (strpos($token[1], "\n") !== false) {
                    $currentLine += substr_count($token[1], "\n");
                }
            }
        }
        $end = $currentLine;

        $this->index = $tmp;

        return new LineRange($start, $end);
    }

    /**
     * @param int $line
     * @return string
     */
    public function getCodeOnLine($line)
    {
        $tmp = $this->index;
        $code = '';

        $this->seekLine($line);
        $this->seekStartOfCodeBlock($line);

        while ($token = $this->next()) {
            if ($token === ';' || (is_array($token) && $token[2] === $line && $this->numLineBreaks($token) > 0)) {
                break;
            }
            $code .= is_array($token) ? $token[1] : $token;
        }

        $this->index = $tmp;

        return $code;
    }

    /**
     * @param $comment
     * @return bool
     */
    public function isSingleLineComment($comment)
    {
        $line = $comment[2];
        $tokens = $this->getLineTokens($line);
        foreach ($tokens as $token) {
            if ($this->isBreakableToken($token)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function nextLineIsBlank()
    {
        $token = $this->current();

        if (false === $token) {
            return false;
        }

        return $this->isWhitespace($token) && substr_count($token[1], "\n") > 1;
    }

    /**
     * @param int $line
     * @return bool
     */
    public function lineIsBreakable($line)
    {
        $tokens = $this->getLineTokens($line);
        foreach ($tokens as $token) {
            if ($this->isBreakableToken($token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $onOrAfterLine
     * @return false|int
     */
    public function getNextBreakableLine($onOrAfterLine)
    {
        $i = 1;
        do {
            $peek = $this->peek($i);
            $i++;
        } while (
            ($peek && !$this->isBreakableToken($peek)) ||
            ($peek && (!is_array($peek) || $peek[2] < $onOrAfterLine))
        );

        return $this->isBreakableToken($peek) ? $peek[2] : false;
    }

    /**
     * @return string|false
     */
    public function getNextAssignedVariable()
    {
        $i = 1;
        do {
            $peek1 = $this->peek($i);
            $peek2 = $this->peek($i + 1);
            $i++;
        } while ($peek1 && $peek2 && !$this->isVariable($peek1) && $peek2 !== '=');

        return $this->isVariable($peek1) ? $peek1[1] : false;
    }

    /**
     * @param int $line
     */
    public function seekLine($line)
    {
        foreach ($this->tokens as $index => $token) {
            if (is_array($token) && $token[2] >= $line) {
                $this->index = $index;
                break;
            }
        }

        while ($token = $this->last()) {
            if (is_array($token) && $token[2] < $line) {
                $this->next();
                break;
            }
        }
    }

    /**
     * @param int $line
     * @return array
     */
    public function getLineTokens($line)
    {
        $ret = [];
        $index = 0;

        foreach ($this->tokens as $i => $token) {
            if (is_array($token) && $token[2] === $line) {
                $index = $i;
                break;
            }
        }

        if ($index >= 1) {
            $index--;
        }

        while (!is_array($this->tokens[$index]) && $index > 0) {
            $index--;
        }
        $index++;

        do {
            $ret[] = $this->tokens[$index];
            $index++;
        } while (
            isset($this->tokens[$index]) &&
            (
                !is_array($this->tokens[$index]) ||
                (is_array($this->tokens[$index]) && $this->tokens[$index][2] === $line && !strpos($this->tokens[$index][1], "\n"))
            )
        );

        return $ret;
    }

    /**
     * @return string|array
     */
    public function current()
    {
        if (!isset($this->tokens[$this->index])) {
            return false;
        }

        return $this->tokens[$this->index];
    }

    /**
     * @return bool|string|array
     */
    public function next()
    {
        if (!isset($this->tokens[$this->index])) {
            return false;
        }

        return $this->tokens[$this->index++];
    }

    /**
     * @return bool|string|array
     */
    public function last()
    {
        if ($this->index === 0) {
            return false;
        }

        return $this->tokens[--$this->index];
    }

    public function reset()
    {
        $this->index = 0;
    }

    /**
     * @param int $advance
     * @return bool|array
     */
    public function peek($advance = 1)
    {
        $i = 1;
        $token = false;
        $hits = 0;

        while ($hits < $advance) {
            if (isset($this->tokens[$this->index + $i])) {
                $token = $this->tokens[$this->index + $i];
                if (!$this->isWhitespace($token)) {
                    $hits++;
                }
                $i++;
            } else {
                return false;
            }
        }

        return $token;
    }

    /**
     * @param $token
     * @return int
     */
    public function numLineBreaks($token)
    {
        return is_array($token) ? substr_count($token[1], "\n") : 0;
    }

    /**
     * @param array|string $token
     * @return bool
     */
    public function isWhitespace($token)
    {
        return is_array($token) && $token[0] === T_WHITESPACE;
    }

    /**
     * @param array|string $token
     * @return bool
     */
    public function isComment($token)
    {
        return is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true);
    }

    /**
     * @param array|string $token
     * @return bool
     */
    public function isVariable($token)
    {
        return is_array($token) && $token[0] === T_VARIABLE;
    }

    /**
     * @param array|string $token
     * @return bool
     */
    public function isBreakableToken($token)
    {
        if (is_array($token) && in_array($token[0], [
                T_COMMENT, T_DOC_COMMENT, T_WHITESPACE, T_OPEN_TAG
            ], true)) {
            return false;
        }

        return true;
    }

    protected function seekStartOfCodeBlock($currentLine)
    {
        while ($token = $this->last()) {
            if (in_array($token, ['{', ';'], true)) {
                $this->next();
                break;
            }

            if (is_array($token)) {
                $currentLine = $token[2];
            }
        }

        do {
            $token = $this->next();
            $currentLine += $this->numLineBreaks($token);
        } while (!$this->isBreakableToken($token));

        $this->last();

        return $currentLine;
    }
}
