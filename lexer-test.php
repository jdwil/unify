<?php

require_once __DIR__ . '/vendor/autoload.php';

$lexerDefinition = new \JDWil\Unify\Lexer\LexerDefinition();
$factory = new \Phlexy\LexerFactory\Stateful\UsingCompiledRegex(
    new \Phlexy\LexerDataGenerator()
);
$lexer = $factory->createLexer($lexerDefinition->create(), 'i');

$tokens = $lexer->lex('Create ../file.txt, /tmp/test.xtx');

print_r($tokens);
