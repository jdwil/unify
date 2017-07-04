# Unify Lexer

[&laquo; Back](../unify.md)

The `UnifyLexerDefinition` contains the token information related to parsing
Unify grammar. This lexer is the most important one in Unify. It allows us
to define a complex grammar without resorting to a thousand clumsy regular
expressions.

### Setup

Example: Setting up a lexer.

[unify]: # (setup, skip)

```php
<?php

use JDWil\Unify\Lexer\UnifyLexerDefinition;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;
use Phlexy\LexerDataGenerator;

$definition = new UnifyLexerDefinition();
$factory = new UsingCompiledRegex(new LexerDataGenerator());
$lexer = $factory->createLexer($definition->create(), 'i');
```

### Overview

In its initial state (called 'INITIAL') the lexer will look for character
sequences that begin an assertion or command. For example, to force a
function foo to always return a value, we want our grammar to read
`foo() will return 1234`.

One of the character sequences the lexer looks for in its initial state
is a FUNCTION_CALL, which is defined as

`'[a-zA-Z_]\w*\([^\)]*\)'`

When `foo()` is encountered in the string, the lexer changes its state
to FUNCTION_CALL. In the FUNCTION_CALL state it is now looking for a
different set of character sequences than it would have in INITIAL. For
example, in this state the lexer will now match

`'will( always)? return'`

When encountered, this sequence will map to the token `UT_ALWAYS_RETURN`.
In addition to the above sequence, the lexer is looking for PHP values that,
in this case, would denote WHAT is returned from our function. These are
the common regexes for matching PHP types:

```
    const WHITESPACE = '[ \n\r\t]+';
    const SINGLE_QUOTED_STRING = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';
    const DOUBLE_QUOTED_STRING = '"[^"\\\\${]*(?:(?:\\\\.|\$(?!\{|[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|\{(?!\$))[^"\\\\${]*)*"';
    const INTEGER = '[0-9]+';
    const FLOAT = '[0-9]*\.[0-9]+';
    const TYPE_ARRAY = '(\[|array\().*(\]|\))';
    const CONSTANT = '[a-zA-Z_]\w*\b';
```

Now lets run the lexer on our command and examine the generated tokens.

```php
<?php

$tokens = $lexer->lex('foo() will return 1234');

print_r($tokens);
```

Output:

```stdout
Array
(
    [0] => Array
        (
            [0] => 119
            [1] => 1
            [2] => foo()
        )

    [1] => Array
        (
            [0] => 101
            [1] => 1
            [2] =>  
        )

    [2] => Array
        (
            [0] => 121
            [1] => 1
            [2] => will return
        )

    [3] => Array
        (
            [0] => 101
            [1] => 1
            [2] =>  
        )

    [4] => Array
        (
            [0] => 107
            [1] => 1
            [2] => 1234
        )

)
```

This gives us a simple set of tokens that are easy for our parsers to reason about.
