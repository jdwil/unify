# Unify

Have you ever written a library for use by your fellow co-workers? You slaved over
it for days or weeks, making sure the implementation was perfect. You spent as much
time unit testing it to make sure it was ready for prime-time. Eventually, you are 
either done or you've run out of time and you make it known to your colleagues that 
this fantastic new library is available... but the response is luke-warm at best.

Why?

Often, it's because the documentation wasn't thorough enough. People may love the
code, but they don't have time to reverse-engineer it. The troubling part is that
often, we don't have, or aren't given enough, time to write it either.

Unify attempts to solve this by combining documentation with automated testing. If
this sounds interesting to you, read on...

## Examples

```php
// 1
$x = 1;

/**
 * @var int $a
 */
function foo($a) {
    return $a * 2;
}

// 2
$x = foo(1);

/* or we can do */

$x = foo(1); // $x = 2
$x = foo(1); // $x == 2
$x = foo(1); // $x === 2

// $x == 2
$x = foo(1);

$x = foo('1'); // fail
```
