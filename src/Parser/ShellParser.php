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

use JDWil\Unify\Assertion\Shell\Core\AssertCommandOutputEquals;
use JDWil\Unify\Assertion\Shell\ShellAssertionQueue;
use Phlexy\Lexer\Stateful;

class ShellParser
{
    private $file;

    private $lexer;

    private $assertions;

    private $command;

    public function __construct($file, Stateful $lexer)
    {
        $this->file = $file;
        $this->lexer = $lexer;
        $this->assertions = new ShellAssertionQueue();
    }

    public function parse($code)
    {
        $tokens = $this->lexer->lex($code);

        $command = $expectedOutput = $line = null;
        foreach ($tokens as $token) {
            switch ($token[0]) {
                case SH_COMMAND:
                    $command = $token[2];
                    break;

                case SH_COMMAND_OUTPUT:
                    $expectedOutput = $token[2];
                    $line = $token[1];
                    break;
            }
        }

        if ($command) {
            if (null === $expectedOutput) {
                $expectedOutput = '';
            }
            $this->assertions->add(new AssertCommandOutputEquals($expectedOutput, $this->file, $line));
            $this->command = $command;
        }
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getAssertions()
    {
        return $this->assertions;
    }
}
