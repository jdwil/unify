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

require_once __DIR__ . '/vendor/autoload.php';

$lexerDefinition = new \JDWil\Unify\Lexer\MarkdownLexerDefinition();
$factory = new \Phlexy\LexerFactory\Stateful\UsingCompiledRegex(
    new \Phlexy\LexerDataGenerator()
);
$lexer = $factory->createLexer($lexerDefinition->create(), 'is');

/*
$string = "foo();\nbar();\n```";
echo preg_match("~(.*?)(?=```)~Ais", $string, $m) . "\n";
var_dump($m);
die();
*/

$text = <<<_TEXT_
Some markdown text here

[unify]: # (skip)
```php
foo();
bar();
```

Here is some more text.
_TEXT_;
$text = file_get_contents('docs/markdown/Unify.md');

$tokens = $lexer->lex($text);

print_r($tokens);
