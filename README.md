# Unify

#### What's this thing?

Unify is a tool meant to make your life easier. It allows you to write tests and
documentation at the same time, greatly reducing the time and effort spent
delivering software. Unify makes a great complement to BDD processes and tools,
like Behat. Behat is a fantastic way to document your system with regression testing.
Unify aims to fill a similar niche, but on the unit and integration testing side, 
with a more developer-centric bent.

## Example

Below is an actual test. While intentionally simple, when Unify is run on its
own project documentation, it will evaluate the code block below and assert
that $x does, in fact, equal 'Zm9v'.

```php
<?php

$x = Encoder::encode('foo'); // $x is 'Zm9v'

class Encoder
{
    public static function encode($string)
    {
        return base64_encode($string);
    }
}
```

## Documentation

The full user documentation can be found 
[here](docs/markdown/user/unify.md).

The full developer documentation can be found 
[here](docs/markdown/developer/unify.md).

## Roadmap

See the current roadmap [here](docs/roadmap.md)

## Credits

Like most FOSS, this library was built on the backs of some incredible community
projects. Special thanks to everyone involved in the projects below:

- [Phlexy](https://github.com/nikic/Phlexy)    
- [ReactPHP](https://github.com/reactphp/react)
- [Symfony](https://github.com/symfony/symfony)
- [PHPUnit Code Coverage](https://github.com/sebastianbergmann/php-code-coverage)
- [Runkit](https://github.com/zenovich/runkit)
- [Runkit7](https://github.com/TysonAndre/runkit7)
