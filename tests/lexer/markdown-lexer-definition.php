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

require_once __DIR__ . '/../../vendor/autoload.php';

use JDWil\Unify\Lexer\MarkdownLexerDefinition;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;
use Phlexy\LexerDataGenerator;

$definition = new MarkdownLexerDefinition();
$factory = new UsingCompiledRegex(new LexerDataGenerator());
$lexer = $factory->createLexer($definition->create(), 'is');

/**
 * Parse a PHP code block.
 */

/*
 * $tokens[0][0] is MD_MARKDOWN.
 * $tokens[1][0] is MD_PHP_OPEN.
 * $tokens[2][0] is MD_PHP_CODE.
 * $tokens[3][0] is MD_CLOSE_CODE.
 * $tokens[4][0] is MD_MARKDOWN.
 */
$tokens = $lexer->lex('
```php
<?php

\$x = 1;
```
');

/**
 * Parse a Shell code block.
 */

/*
 * $tokens[0][0] is MD_MARKDOWN.
 * $tokens[1][0] is MD_SHELL_OPEN.
 * $tokens[2][0] is MD_SHELL_CODE.
 * $tokens[3][0] is MD_CLOSE_CODE.
 * $tokens[4][0] is MD_MARKDOWN.
 */
$tokens = $lexer->lex('
```shell
$ echo "foo"
foo
```
');

/**
 * Parse a generic code block.
 */

/*
 * $tokens[0][0] is MD_MARKDOWN.
 * $tokens[1][0] is MD_OTHER_OPEN.
 * $tokens[2][0] is MD_OTHER_CODE.
 * $tokens[3][0] is MD_CLOSE_CODE.
 * $tokens[4][0] is MD_MARKDOWN.
 */
$tokens = $lexer->lex('
```
foo
```
');

/**
 * Parse a stdout block
 */

/*
 * $tokens[0][0] is MD_MARKDOWN.
 * $tokens[1][0] is MD_STDOUT_OPEN.
 * $tokens[2][0] is MD_STDOUT.
 * $tokens[3][0] is MD_CLOSE_CODE.
 * $tokens[4][0] is MD_MARKDOWN.
 */
$tokens = $lexer->lex('
```stdout
foo
```
');

exit(0);
