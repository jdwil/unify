# Shell Lexer

[&laquo; Back](../unify.md)

The `ShellLexerDefinition` contains the token information relating to the grammar used to describe Shell commands. It's the
simplest lexer in Unify. When Unify encounters a Shell command in a documentation parser, it sends its code through
the lexer before parsing its contents.

Example:

```php
<?php

use JDWil\Unify\Lexer\ShellLexerDefinition;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;
use Phlexy\LexerDataGenerator;

$definition = new ShellLexerDefinition();
$factory = new UsingCompiledRegex(new LexerDataGenerator());
$lexer = $factory->createLexer($definition->create(), 'is');

$shellCode = <<<CODE
$ echo "foo"
foo
CODE;

$tokens = $lexer->lex($shellCode);

$type = 0;
$line = 1;
$value = 2;

/*
 * $tokens[0][$type] equals SH_COMMAND_OPEN. All shell grammar tokens are defined under SH_*. Likewise:
 * $tokens[1][$type] equals SH_COMMAND.
 * $tokens[2][$type] equals SH_COMMAND_END.
 * $tokens[3][$type] equals SH_COMMAND_OUTPUT.
 * 
 * $tokens[0][$line] is 1. This is the line number.
 * $tokens[0][$value] is '$'. This is the actual code parsed by the lexer.
 * 
 * See the raw output below.
 */

print_r($tokens);
```

Output:

```stdout
Array
(
    [0] => Array
        (
            [0] => 100
            [1] => 1
            [2] => $
        )

    [1] => Array
        (
            [0] => 101
            [1] => 1
            [2] =>  echo "foo"
        )

    [2] => Array
        (
            [0] => 102
            [1] => 1
            [2] => 

        )

    [3] => Array
        (
            [0] => 103
            [1] => 2
            [2] => foo
        )

)
```

