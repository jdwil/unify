# Markdown Lexer

[&laquo; Back](../unify.md)

The `MarkdownLexerDefinition` contains the token information related to parsing
markdown files.

### Setup

Example: Setting up a Markdown lexer.

[unify]: # (setup, skip)

```php
<?php

use JDWil\Unify\Lexer\MarkdownLexerDefinition;
use Phlexy\LexerFactory\Stateful\UsingCompiledRegex;
use Phlexy\LexerDataGenerator;

$definition = new MarkdownLexerDefinition();
$factory = new UsingCompiledRegex(new LexerDataGenerator());
$lexer = $factory->createLexer($definition->create(), 'is');
```

### Directives

Unify supports directives via comments in the markdown. For example, you can tell Unify
to skip the testing of a code block with the `skip` directive.

Example: Tokenizing the `skip` directive.

```php
<?php

$markdown = <<<CODE
[unify]: # (skip)
CODE;

$tokens = $lexer->lex($markdown);

/*
 * MD_DIRECTIVE tokens represent the parsed directives ('skip' in this example).
 * $tokens[1][0] equals MD_DIRECTIVE.
 */

print_r($tokens);
```

Output:

```stdout
Array
(
    [0] => Array
        (
            [0] => 105
            [1] => 1
            [2] => [unify]: # (
        )

    [1] => Array
        (
            [0] => 103
            [1] => 1
            [2] => skip
        )

    [2] => Array
        (
            [0] => 106
            [1] => 1
            [2] => )
        )

)
```

### Code Blocks

The Markdown lexer will identify specific types of code blocks, namely PHP, Shell and 
STDOUT.

Example: Code blocks in Unify markdown.

```
    ```php
        php code
    ```
    
    ```shell
        shell commands
    ```
    
    ```stdout
        raw text
    ```
```
