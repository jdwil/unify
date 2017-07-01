# Test Doubles

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
