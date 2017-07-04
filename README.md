# Unify

#### Forget about testing.

Unify is a tool meant to make your life easier. It allows you to write tests and
documentation at the same time, greatly reducing the time and effort spent
delivering software. Unify makes a great complement to BDD processes and tools,
like Behat.

Unify fits naturally with how we, as developers, approach software development.
Write your amazing new app and document your API, providing plenty of examples. Your
users and coworkers will love you and, amazingly, your code will be tested. You'll
completely forget that you were writing tests the whole time. Unfortunately, we can't
help you forget about all the time you slaved over unit tests before Unify.

## Documentation

The full user documentation can be found 
[here](docs/markdown/user/unify.md).

The full developer documentation can be found 
[here](docs/markdown/developer/unify.md).

## Roadmap

1. Possible new assertions:
    1. File contains
    1. is (type)
    1. is not a number (?)
    1. matches regex
    1. ends with ...
    1. begins with ...
    1. Anything else?
1. Make search directories and file types configurable.
1. Clean up verbose and very verbose output for `unify run`
1. Better runkit support:
    1. Proc returns value depending on arguments.
    1. Proc returns value on nth call.
    1. Proc returns value on user-defined condition.
1. Support for phpdbg as an alternative to xdebug.
1. Implement patchwork as a user-land fallback when runkit is not installed.
1. Code Coverage support
1. Need adopters and contributors
1. Add support for other Github-supported markups. Options (what are people using for PHP?):
    1. RedCloth (.textile)
    1. .org (orgmode)
    1. creole
    1. MediaWiki
    1. Sphinx
    1. Asciidoc
    1. Pod
1. Explore assertions in production code.
1. Sequence Analysis.
1. PHPDocumentor support (???)
