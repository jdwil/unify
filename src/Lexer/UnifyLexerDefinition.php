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
define('UT_ARRAY_CONTAINS_KEY', 122);
define('UT_ARRAY_NOT_CONTAINS_KEY', 123);
define('UT_HAS_ITERATIONS', 124);
define('UT_ITERATION', 125);
define('UT_OBJECT_HAS_PROPERTY', 126);
define('UT_OBJECT_NOT_HAS_PROPERTY', 127);
define('UT_PROPERTY_REFERENCE', 128);
define('UT_NOT_EQUALS', 129);
define('UT_NOT_EQUALS_MATCH_TYPE', 130);
define('UT_ARRAY', 131);
define('UT_CONSTANT', 132);
define('UT_ARRAY_CONTAINS', 133);
define('UT_DESCRIPTOR', 134);
define('UT_EMPTY', 135);
define('UT_NOT_EMPTY', 136);
define('UT_CONTAINS_ONLY', 137);
define('UT_IS_READABLE', 138);
define('UT_IS_NOT_READABLE', 139);
define('UT_IS_WRITABLE', 140);
define('UT_IS_NOT_WRITABLE', 141);
define('UT_BLOCK_QUOTE', 142);

/**
 * Class UnifyLexerDefinition
 *
 * @todo check the file path stuff. We should probably remove quoted file paths entirely and use quoted strings instead.
 */
class UnifyLexerDefinition implements LexerDefinitionInterface
{
    const VARIABLE = '\$[a-zA-Z_]\w*(\[[^\]]+\])*';
    const WHITESPACE = '[ \n\r\t]+';
    const SINGLE_QUOTED_STRING = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';
    const DOUBLE_QUOTED_STRING = '"[^"\\\\${]*(?:(?:\\\\.|\$(?!\{|[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|\{(?!\$))[^"\\\\${]*)*"';
    const INTEGER = '[0-9]+';
    const FLOAT = '[0-9]*\.[0-9]+';
    const TYPE_ARRAY = '(\[|array\()((?>[^()\[\]]*)|(?R))*(\]|\))';
    const CONSTANT = '[a-zA-Z_]\w*\b';
    //const QUOTED_FILE_PATH = '[\'"][^\'"]+[\'"]';
    const UNQUOTED_FILE_PATH = '\.?\.?\/[^\s,;]+';
    const INLINE_COMMENT = '\([^\)]*\)';
    const COMMENT = '(.*)?[:;\.]';
    const FUNCTION_CALL = '[a-zA-Z_]\w*\([^\)]*\)';
    const METHOD_CALL = '\$?[a-zA-Z_]\w*(::)?(->)?[a-zA-Z_]\w*\([^\)]*\)';
    const PROPERTY_REFERENCE = '\$?[a-zA-Z_]\w*::[a-zA-Z_]\w*\b';
    const END_STATEMENT = '[;\.]';
    const ITERATIONS = 'on( iterations?)?';

    /**
     * @return array
     */
    public function create()
    {
        $procedureCall = [
            self::WHITESPACE => UT_WHITESPACE,
            self::INLINE_COMMENT => UT_COMMENT,

            self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
            self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,
            self::FLOAT => UT_FLOAT,
            self::INTEGER => UT_INTEGER,
            self::TYPE_ARRAY => UT_ARRAY,

            'is empty' => UT_EMPTY,
            'is not empty' => UT_NOT_EMPTY,
            'will( always)? return' => UT_ALWAYS_RETURN,

            self::END_STATEMENT => function (Stateful $lexer) {
                $lexer->swapState('INITIAL');

                return UT_END_FUNCTION_CALL;
            }
        ];

        $iterations = function (Stateful $lexer) {
            $lexer->swapState('ITERATIONS');

            return UT_HAS_ITERATIONS;
        };

        $endAssertion = function (Stateful $lexer) {
            $lexer->swapState('INITIAL');

            return UT_END_ASSERTION;
        };

        $filePath = function (Stateful $lexer) {
            $lexer->swapState('PATH');

            return UT_FILE_PATH;
        };

        $inVariable = [

            // Internal types
            self::WHITESPACE => UT_WHITESPACE,
            self::INLINE_COMMENT => UT_COMMENT,
            self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
            self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,
            self::FLOAT => UT_FLOAT,
            self::INTEGER => UT_INTEGER,
            self::TYPE_ARRAY => UT_ARRAY,
            self::VARIABLE => UT_VARIABLE,

            ':' => function (Stateful $lexer) {
                $lexer->swapState('BLOCK_TEXT');

                return UT_BLOCK_QUOTE;
            },

            'is empty' => UT_EMPTY,
            'is not empty' => UT_NOT_EMPTY,

            // Arrays
            'has key' => UT_ARRAY_CONTAINS_KEY,
            'is set' => UT_ARRAY_CONTAINS_KEY,
            'contains only' => UT_CONTAINS_ONLY,

            // Objects
            'has property' => UT_OBJECT_HAS_PROPERTY,
            'doesn?\'?t?( not)? have property' => UT_OBJECT_NOT_HAS_PROPERTY,

            // Inequality
            '\!==' => UT_NOT_EQUALS_MATCH_TYPE,
            '\!=' => UT_NOT_EQUALS,
            'is not' => UT_NOT_EQUALS,
            'doesn?\'?t?( not)? equal' => UT_NOT_EQUALS,
            'no longer equals' => UT_NOT_EQUALS,

            // Equality
            '===' => UT_EQUALS_MATCH_TYPE,
            '==?' => UT_EQUALS,
            'is greater than or equal to' => UT_GREATER_THAN_OR_EQUAL,
            'is greater than' => UT_GREATER_THAN,
            '>=' => UT_GREATER_THAN_OR_EQUAL,
            '>' => UT_GREATER_THAN,
            'is less than or equal to' => UT_LESS_THAN_OR_EQUAL,
            'is less than' => UT_LESS_THAN,
            '<=' => UT_LESS_THAN_OR_EQUAL,
            '<' => UT_LESS_THAN,
            'is equal to' => UT_EQUALS,
            'is' => UT_EQUALS,
            'now equals' => UT_EQUALS,
            'equals|returns' => UT_EQUALS,

            // Arrays
            'contains|has' => function (Stateful $lexer) {
                $lexer->swapState('ARRAY_CONTAINS');

                return UT_ARRAY_CONTAINS;
            },

            // Misc
            self::ITERATIONS => $iterations,
            ',' => UT_MORE,
            self::END_STATEMENT => $endAssertion,
            self::CONSTANT => UT_CONSTANT,
        ];

        return [
            'INITIAL' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::INLINE_COMMENT => UT_COMMENT,

                self::METHOD_CALL => function (Stateful $lexer) {
                    $lexer->swapState('METHOD_CALL');

                    return UT_METHOD_CALL;
                },
                self::PROPERTY_REFERENCE => function (Stateful $lexer) {
                    $lexer->swapState('PROPERTY_REFERENCE');

                    return UT_PROPERTY_REFERENCE;
                },
                self::VARIABLE => function (Stateful $lexer) {
                    $lexer->swapState('IN_VARIABLE');

                    return UT_VARIABLE;
                },
                self::FUNCTION_CALL => function (Stateful $lexer) {
                    $lexer->swapState('FUNCTION_CALL');

                    return UT_FUNCTION_CALL;
                },
                //self::QUOTED_FILE_PATH => $filePath,
                self::UNQUOTED_FILE_PATH => $filePath,

                'creates?( files?)?' => function (Stateful $lexer) {
                    $lexer->swapState('IN_FILE');

                    return UT_FILE_EXISTS;
                },

                'deletes?( files?)?' => function (Stateful $lexer) {
                    $lexer->swapState('IN_FILE');

                    return UT_FILE_NOT_EXISTS;
                },

                self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::FLOAT => UT_FLOAT,
                self::INTEGER => UT_INTEGER,
                self::TYPE_ARRAY => UT_ARRAY,
            ] + $inVariable +
                [
                    self::CONSTANT => UT_CONSTANT,
                    self::COMMENT => UT_COMMENT,
                ],

            'FUNCTION_CALL' => $procedureCall,
            'METHOD_CALL' => $procedureCall,
            'IN_VARIABLE' => $inVariable,

            'BLOCK_TEXT' => [
                '.*' => UT_QUOTED_STRING
            ],

            'PROPERTY_REFERENCE' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::INLINE_COMMENT => UT_COMMENT,
                self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,

                'is empty' => UT_EMPTY,
                'is not empty' => UT_NOT_EMPTY,
                'exists' => UT_OBJECT_HAS_PROPERTY,
                'doesn?\'?t?( not)? exist' => UT_OBJECT_NOT_HAS_PROPERTY,

                self::END_STATEMENT => $endAssertion
            ],

            'IN_FILE' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::INLINE_COMMENT => UT_COMMENT,
                self::SINGLE_QUOTED_STRING => UT_FILE_PATH,
                self::DOUBLE_QUOTED_STRING => UT_FILE_PATH,
                //self::QUOTED_FILE_PATH => UT_FILE_PATH,
                self::UNQUOTED_FILE_PATH => UT_FILE_PATH,
                ',' => UT_MORE,
                self::END_STATEMENT => $endAssertion
            ],

            'ITERATIONS' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::INLINE_COMMENT => UT_COMMENT,
                '\d+' => UT_ITERATION,
                ',' => UT_MORE,
                self::END_STATEMENT => $endAssertion,
            ],

            'ARRAY_CONTAINS' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::SINGLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::DOUBLE_QUOTED_STRING => UT_QUOTED_STRING,
                self::FLOAT => UT_FLOAT,
                self::INTEGER => UT_INTEGER,
                self::TYPE_ARRAY => UT_ARRAY,
                self::VARIABLE => UT_VARIABLE,

                'items?|elements?|values?' => UT_DESCRIPTOR,

                self::ITERATIONS => $iterations,
                ',' => UT_MORE,
                self::END_STATEMENT => $endAssertion,
            ],

            'PATH' => [
                self::WHITESPACE => UT_WHITESPACE,
                'exists' => UT_FILE_EXISTS,
                'doesn?\'?t?( not)? exist' => UT_FILE_NOT_EXISTS,
                'is readable' => UT_IS_READABLE,
                'isn?\'?t?( not)? readable' => UT_IS_NOT_READABLE,
                'is writable' => UT_IS_WRITABLE,
                'isn?\'?t?( not)? writable' => UT_IS_NOT_WRITABLE,

                self::ITERATIONS => $iterations,
                ',' => UT_MORE,
                self::END_STATEMENT => $endAssertion,
            ]
        ];
    }
}
