<?php

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

class LexerDefinition
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

    public function create()
    {
        return [
            'INITIAL' => [
                self::WHITESPACE => UT_WHITESPACE,
                self::COMMENT => UT_COMMENT,
                self::VARIABLE => function (Stateful $lexer) {
                    $lexer->swapState('IN_VARIABLE');

                    return UT_VARIABLE;
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
                ';' => function (Stateful $lexer) {
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
                ';' => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return UT_END_ASSERTION;
                }
            ]
        ];
    }
}
