# Unify Documentation

Welcome to the Unify docs. Unify is a new testing framework that is meant to fill
a perceived void in the world of behavior-driven development (BDD). This does not
mean the library is limited to use in BDD environments. That's just where it "got
it's roots," so to say.

## Table of Contents

1. [Quick Start (this document)](#overview)
  
<a name="overview"/>

## Quick Start

It only takes a moment to install and set up Unify, so let's get cracking! 

### Install Unify

To install Unify with composer, run:

[unify]: # (skip)
```shell
composer require jdwil/unify
```

You may want to create a symlink in the bin/ directory of your root project
path for the unify executable.

Unify runs fine for most setups out of the box, with no configuration, so we'll
use all default settings for this guide. More details about the configuration
can be found in the [Configuration] section.

### Run Unify

```shell
$ ./bin/unify --help -q
```

### Wrapping Up

So that's it, we're done with the guide. Wait, oh you have questions... No, we
did write a test. It's in the step above. Okay, I'm just being silly now :) The
above example is not your typical use-case, but it IS a test. Let me explain.

Unify parses your markdown files (or other files, as we'll see) looking for code
to execute. The above code is tagged as "shell" like so:

```markdown
    ```shell
    $ ./vendor/bin/unify --help -q
    ```
```

When Unify sees you have code blocks tagged with shell it will parse that text and
try to interpret it. This example is intentionally simple. "$" denotes the start
of a command, which Unify will execute. The remaining lines are the expected output
of the command. In this case, nothing, since we ran with the quiet flag.

The following would work as well:

```shell
$ ./bin/unify --help
Usage:
  list [options] [--] [<namespace>]

Arguments:
  namespace            The namespace name

Options:
      --raw            To output raw command list
      --format=FORMAT  The output format (txt, xml, json, or md) [default: "txt"]

Help:
  The list command lists all commands:
  
    php ./bin/unify list
  
  You can also display the commands for a specific namespace:
  
    php ./bin/unify list test
  
  You can also output the information in other formats by using the --format option:
  
    php ./bin/unify list --format=xml
  
  It's also possible to get raw list of commands (useful for embedding command runner):
  
    php ./bin/unify list --raw
```

### Get on With the PHP

Right, you're here to test PHP after all. Well, it works very similarly. A stupid
simple test:

```php
<?php

$x = foo(1); // $x is 2

function foo($a) {
    return $a * 2;
}
```

Here we set $x to the result of foo(2), which we rightly expect to be 2 as described
in the comment. Now when we run Unify it will execute the above code and assert
that $x does indeed equal 2.

[unify]: # (skip)
```shell
$ ./bin/unify run docs/markdown/Unify.md
 1/1 [============================] 100%

 SUCCESS
 1 file(s). 1 Assertions. 1/1 Passed.
```

Unify has a flexible grammar and the above assertion can be written in different
ways to suit your taste. All of the below are identical:

```php
<?php

$x = foo(1); // $x is 2
$x = foo(1); // $x = 2
$x = foo(1); // $x == 2
$x = foo(1); // $x === 2
$x = foo(1); // $x equals 2
$x = foo(1); // $x is equal to 2

// $x is 2
$x = foo(1);

/**
 * $x is 2 
 */
$x = foo(1);

$x = foo(1); /* $x is 2 */
$x = foo(1); # $x is 2

// etc...

function foo($a) {
    return $a * 2;
}
```

This is the real power of Unify. Just by typing the example above, to help you understand
how Unify works, I've tested part of Unify itself. Every time I run my Unify test
suite, it will execute the above block and validate all my assertions and, in a
roundabout way, test that my documentation is correct too!

Here's what it looks like when I run this entire file:

```
$ ./bin/unify run docs/markdown/Unify.md 
 4/4 [============================] 100%

 SUCCESS
 4 Test Plan(s). 13 Assertions. 13/13 Passed.
```

I'll wait while you count the assertions in this file. ;) j/k. I won't wait.

### Wrapping Up (Seriously, this time)

This quick start was just to give you a small taste of Unify. If it's not for you that's
completely understandable, and all our best to you and yours. If, however, you want to
continue learning about Unify, check out the links in the table of contents above
to read the full documentation.
