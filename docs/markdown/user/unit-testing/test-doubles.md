# Test Doubles

> Note that the examples in this section require [Runkit](https://github.com/zenovich/runkit)
or, if you're running PHP >= 7.0, [Runkit7](https://github.com/TysonAndre/runkit7). While
runkit will likely remain the preferred method, there are plans on the roadmap to implement
patchwork as a user-land fallback for this functionality.

> "Test Double" support is currently limited to forcing certain return values for functions
and methods. There are plans to beef up this support to include conditional return values
based on parameters or other criteria as well as "spy" support.

Unify doesn't support test doubles in the same vein as you would use them in
PHPUnit or PHPSpec. However, the same functionality can be achieved.

Say you need to force a function to return a particular value. You can use the
`x() will (always) return y` syntax for this:

```php
<?php

/**
 * bar() will return 1
 */

$x = bar(); // $x is 1

function bar() {
    return 5000;
}
```

Likewise, for class methods you can do things like this:

```php
<?php

/**
 * Foo::bar() will return 1
 */

$foo = new Foo();
$x = $foo->bar(); // $x is 1

class Foo
{
    public function bar()
    {
        return 5000;
    }
}
```
