<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\Lexer;

use Phlexy\Lexer\Stateful;

define('UT_VARIABLE', 100);
define('UT_WHITESPACE', 101);
define('UT_QUOTED_STRING', 102);
define('UT_EQUALS', 103);
define('UT_EQUALS_MATCH_TYPE', 104);
define('UT_MORE', 105);
define('UT_END_ASSERTION', 106);
define('UT_INTEGER', 107);
define('UT_FLOAT', 108);
define('UT_FILE_EXISTS', 109);
define('UT_FILE_NOT_EXISTS', 110);
define('UT_GREATER_THAN', 111);
define('UT_GREATER_THAN_OR_EQUAL', 112);
define('UT_LESS_THAN', 113);
define('UT_LESS_THAN_OR_EQUAL', 114);
define('UT_FILE_PATH', 115);
define('UT_COMMENT', 116);
define('UT_FUNCTION_CALL', 117);
define('UT_END_FUNCTION_CALL', 118);
define('UT_METHOD_CALL', 119);
define('UT_END_METHOD_CALL', 120);
define('UT_ALWAYS_RETURN', 121);

/**
 * Class UnifyLexerDefinition
 */
class UnifyLexerDefinition implements LexerDefinitionInterface
{
    const VARIABLE = '\$[a-zA-Z_]\w*';
    const WHITESPACE = '[ \n\r\t]+';
    const SINGLE_QUOTED_STRING = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';
    const DOUBLE_QUOTED_STRING = '"[^"\\\\${]*(?:(?:\\\\.|\$(?!\{|[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|\{(?!\$))[^"\\\\${]*)*"';
    const INTEGER = '[0-9]+';
    const FLOAT = '[0-9]*\.[0-9]+';
    const QUOTED_FILE_PATH = '[\'"][^\'"]+[\'"]';
    const UNQUOTED_FILE_PATH = '\.?\.?\/[^\s,;]+';
    const COMMENT = '\([^\)]*\)';
    const FUNCTION_CALL = '[a-zA-Z_]\w*\([^\)]*\)';
    const METHOD_CALL = '\$?[a-zA-Z_]\w*(::)?(->)?[a-zA-Z_]\w*\([^\)]*\)';
    const END_STATEMENT = '[;\.]';

    /**
     * @return array
     */
    public function create()
    {
        $procedureCall = [
            self::WHITESPACE => UT_WHITESPACE,
            self::COMMENT => UT_COMMENT,
            'will( always)? return' => UT_ALWAYS_RETURN,
            self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
            self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,
            self::FLOAT => UT_FLOAT,
            self::INTEGER => UT_INTEGER,
            self::END_STATEMENT => function (Stateful $lexer) {
                $lexer->swapState('INITIAL');

                return UT_END_FUNCTION_CALL;
            }
        ];

        return [
            'INITIAL' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::COMMENT => UT_COMMENT,
                self::VARIABLE => function (Stateful $lexer) {
                    $lexer->swapState('IN_VARIABLE');

                    return UT_VARIABLE;
                },
                self::FUNCTION_CALL => function (Stateful $lexer) {
                    $lexer->swapState('FUNCTION_CALL');

                    return UT_FUNCTION_CALL;
                },
                self::METHOD_CALL => function (Stateful $lexer) {
                    $lexer->swapState('METHOD_CALL');

                    return UT_METHOD_CALL;
                },

                'creates?( files?)?' => function (Stateful $lexer) {
                    $lexer->swapState('IN_FILE');

                    return UT_FILE_EXISTS;
                },

                'deletes?( files?)?' => function (Stateful $lexer) {
                    $lexer->swapState('IN_FILE');

                    return UT_FILE_NOT_EXISTS;
                }
            ],

            'FUNCTION_CALL' => $procedureCall,
            'METHOD_CALL' => $procedureCall,

            'IN_VARIABLE' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::COMMENT => UT_COMMENT,
                self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::FLOAT => UT_FLOAT,
                self::INTEGER => UT_INTEGER,
                '===' => UT_EQUALS_MATCH_TYPE,
                '==?' => UT_EQUALS,
                '>' => UT_GREATER_THAN,
                'is greater than' => UT_GREATER_THAN,
                '>=' => UT_GREATER_THAN_OR_EQUAL,
                'is greater than or equal to' => UT_GREATER_THAN_OR_EQUAL,
                '<' => UT_LESS_THAN,
                'is less than' => UT_LESS_THAN,
                '<=' => UT_LESS_THAN_OR_EQUAL,
                'is less than or equal to' => UT_LESS_THAN_OR_EQUAL,
                'is equal to' => UT_EQUALS,
                'is' => UT_EQUALS,
                'equals' => UT_EQUALS,
                ',' => UT_MORE,
                self::END_STATEMENT => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return UT_END_ASSERTION;
                }
            ],

            'IN_FILE' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::COMMENT => UT_COMMENT,
                self::QUOTED_FILE_PATH => UT_FILE_PATH,
                self::UNQUOTED_FILE_PATH => UT_FILE_PATH,
                ',' => UT_MORE,
                self::END_STATEMENT => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return UT_END_ASSERTION;
                }
            ]
        ];
    }
}
