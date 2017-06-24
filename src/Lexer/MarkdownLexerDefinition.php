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

namespace JDWil\Unify\Lexer;

use Phlexy\Lexer\Stateful;

define('MD_MARKDOWN', 100);
define('MD_OPEN_CODE', 101);
define('MD_CLOSE_CODE', 102);
define('MD_DIRECTIVE', 103);
define('MD_LANGUAGE', 104);
define('MD_DIRECTIVE_OPEN', 105);
define('MD_DIRECTIVE_CLOSE', 106);
define('MD_PHP_OPEN', 200);
define('MD_PHP_CODE', 201);
define('MD_SHELL_OPEN', 202);
define('MD_SHELL_CODE', 203);
define('MD_OTHER_OPEN', 204);
define('MD_OTHER_CODE', 205);

class MarkdownLexerDefinition implements LexerDefinitionInterface
{
    const PHP_CODE = '```php\n';
    const SHELL_CODE = '```(shell)|(bash)';
    const CODE_BLOCK = '```';
    const DIRECTIVE = '\[unify\]:\s+#\s+\(';

    /**
     * @return array
     */
    public function create()
    {
        return [
            'INITIAL' => [
                self::PHP_CODE => function (Stateful $lexer) {
                    $lexer->swapState('PHP');

                    return MD_PHP_OPEN;
                },
                self::SHELL_CODE => function (Stateful $lexer) {
                    $lexer->swapState('SHELL');

                    return MD_SHELL_OPEN;
                },
                self::DIRECTIVE => function (Stateful $lexer) {
                    $lexer->swapState('DIRECTIVE');

                    return MD_DIRECTIVE_OPEN;
                },
                self::CODE_BLOCK => function (Stateful $lexer) {
                    $lexer->swapState('OTHER_CODE');

                    return MD_OTHER_OPEN;
                },
                '[^\s]+\b' => MD_MARKDOWN,
                '.' => MD_MARKDOWN
            ],

            'PHP' => [
                self::CODE_BLOCK => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return MD_CLOSE_CODE;
                },
                '(.*?)(?=```)' => MD_PHP_CODE
            ],

            'SHELL' => [
                self::CODE_BLOCK => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return MD_CLOSE_CODE;
                },
                '(.*?)(?=```)' => MD_SHELL_CODE
            ],

            'OTHER_CODE' => [
                self::CODE_BLOCK => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return MD_CLOSE_CODE;
                },
                '(.*?)(?=```)' => MD_OTHER_CODE
            ],

            'DIRECTIVE' => [
                '[^\)]+' => MD_DIRECTIVE,
                '\)' => function (Stateful $lexer) {
                    $lexer->swapState('INITIAL');

                    return MD_DIRECTIVE_CLOSE;
                }
            ]
        ];
    }
}
