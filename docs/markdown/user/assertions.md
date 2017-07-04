# Unify Documentation - Assertions

[&laquo; Back](unify.md)

## Table of Contents

1. [Arrays](#arrays)
    1. [Array Contains](#array-contains)
    1. [Array Contains Only](#array-contains-only)
    1. [Array Count](#array-count)
    1. [Array Has Key](#array-has-key)
    1. [Array is (not) Empty](#array-empty)
1. [Classes](#classes)
    1. [Class Has Property](#class-has-property)
    1. [Class Doesn't Have Property](#class-lacks-property)
1. [Equality](#equality)
    1. [Variables](#variable-equality)
1. [File System](#filesystem)
    1. [Exists](#exists)
    1. [Doesn't Exist](#not-exists)
    1. [Readable / Not Readable](#readable)
    1. [Writable / Not Writable](#writable)
  
<a name="arrays" />

## Arrays

<a name="array-contains" />

#### Array Contains

You can assert that an array contains certain elements, including another array.

```php
<?php

/*
 * $x contains 'foo'.
 * $x contains 1000.
 * $x contains ['a', 'b', 'c'].
 */
$x = [
    0 => 'foo',
    1 => 1000,
    2 => [
        'a', 'b', 'c'
    ]
];
```

<a name="array-contains-only" />

#### Array Contains Only

```php
<?php

$a = [1, 2, 3];     // $a contains only integers
$b = ['a', 'b'];    // $b contains only strings
$c = [0.1, 0.2];    // $c contains only floats
$d = [new Foo()];   // $d contains only Foo
$e = [[0], [1]];    // $e contains only arrays
$f = [1, 1.2];      // $f contains only numbers

class Foo {}
```

<a name="array-count" />

#### Array Count

Ensure an array has the proper number of elements.

```php
<?php

$x = [1];       // $x has 1 element
$y = [1, 2];    // $y contains 2 elements
```

<a name="array-has-key" />

#### Array Has Key

For single, or multi-dimensional arrays you can use the standard "is set" syntax

`$var['key'] is set`

`$var['key1']['key2'] is set`

For a single-dimension array, you can also use this syntax

`$var has key 'key'`

```php
<?php

/*
 * $x has key 'foo'.
 * $x['foo'] is set
 */
$x = [
    'foo' => 'bar'
];

// $y['foo']['bar'] is set
$y = [
    'foo' => [
        'bar' => 'baz'
    ]
];

for ($i = 0; $i <= 1; $i++) {
    $x = baz($i); // $x has key 'foo' on iteration 1
}

function baz($i) {
    return $i === 0 ? ['foo' => 'bar'] : [];
}
```

<a name="array-empty" />

#### Array is (not) Empty

Empty works the same was as PHP's `empty()` function. It can be used with arrays or non-arrays.

```php
<?php

$x = [];    // $x is empty
$y = null;  // $y is empty

$a = [1];   // $a is not empty
$b = 'foo'; // $b is not empty
```

<a name="classes" />

## Classes

<a name="class-has-property" />

#### Class Has Property

You can check if either a fully qualified class contains a property, or if an instance of a
class contains a property.

```php
<?php

/*
 * Foo::bar exists.
 * $x::bar exists.
 * $x::fiz exists.
 * $x has property 'baz'.
 */
$x = new Foo();

class Foo
{
    public static $fiz;
    private $bar;
    public $baz;
}
```

<a name="class-lacks-property" />

#### Class Doesn't Have Property

```php
<?php

/*
 * Foo::bar doesn't exist.
 * $y::bar does not exist.
 * $y doesn't have property 'bar'.
 */
$y = new Foo();

class Foo
{
    public $zip;
}
```

<a name="equality" />

## Equality

<a name="variable-equality" />

#### Variables

Examples of asserting equality:

```php
<?php

/*
 * $x is 1.
 * $x = 1.
 * $x == 1.
 * $x == '1' ($x === '1' would fail).
 * $x === 1.
 * $x is equal to 1.
 * $x equals 1.
 */
$x = 1;

$z = 'foo'; // $z is 'foo'

/*
 * $a is ['a', 'b'].
 * $a equals array('a', 'b').
 */
$a = ['a', 'b'];

for ($i = 0; $i <= 3; $i++) {
    /*
     * $y equals 0, 1, 2, 3.
     * $y equals 0 on iteration 1.
     * $y is 1 on iteration 2.
     * $y = 2 on iteration 3.
     * $y == 3 on iteration 4.
     */
    $y = $i; 
}
```

Examples of asserting inequality (not equals):

```php
<?php

/*
 * $x is not 2.
 * $x does not equal 2.
 * $x doesn't equal 2.
 * $x != 2.
 * $x !== 2.
 * $x !== '1'
 */
$x = 1;

/*
 * $a is not ['a', 'c'].
 * $a != ['a', 'c'].
 */
$a = ['a', 'b'];

for ($i = 0; $i <= 3; $i++) {
    /*
     * $y does not equal 1, 2, 3, 4.
     * $y doesn't equal 1 on iteration 1.
     * $y is not 2 on iteration 2.
     * $y != 3 on iteration 3.
     * $y !== 4 on iteration 4.
     * $y no longer equals 4 on iteration 4.
     */
    $y = $i;
}
```

Examples of asserting less / greater than:

```php
<?php

/*
 * $x is less than 2.
 * $x is less than or equal to 1.
 * $x < 2.
 * $x <= 1.
 */
$x = 1;

/*
 * $y is greater than 1.
 * $y is greater than or equal to 2.
 * $y > 1.
 * $y >= 2.
 */
$y = 2;
```

<a name="filesystem" />

## File System

<a name="exists" />

#### Exists

```php
<?php

use Symfony\Component\Filesystem\Filesystem;

$filesystem = new Filesystem();
/*
 * Create /tmp/test.txt;
 * Creates '/tmp/test.txt'.
 * Create file /tmp/test.txt . (Beware a dot line-ending when using a file path. Notice the space.)
 */
$filesystem->touch('/tmp/test.txt');
$filesystem->remove('/tmp/test.txt');

$filesystem->touch('/tmp/test.txt');    // /tmp/test.txt exists
$filesystem->remove('/tmp/test.txt');   // /tmp/test.txt doesn't exist
```

<a name="not-exists" />

#### Doesn't Exists

```php
<?php

use Symfony\Component\Filesystem\Filesystem;

$filesystem = new Filesystem();
$filesystem->touch('/tmp/test.txt');
/*
 * Delete /tmp/test.txt;
 * Deletes '/tmp/test.txt'.
 * Delete file /tmp/test.txt . (Beware a dot line-ending when using a file path. Notice the space.)
 */
$filesystem->remove('/tmp/test.txt');

$filesystem->touch('/tmp/test.txt');    // /tmp/test.txt exists
$filesystem->remove('/tmp/test.txt');   // /tmp/test.txt doesn't exist
```

<a name="readable" />

#### Readable / Not Readable

```php
<?php

use Symfony\Component\Filesystem\Filesystem;

$filesystem = new Filesystem();

$filesystem->touch('/tmp/test.txt');        // /tmp/test.txt is readable
$filesystem->chmod('/tmp/test.txt', 0222);  // /tmp/test.txt isn't readable
$filesystem->remove('/tmp/test.txt');       // /tmp/test.txt doesn't exist
```

<a name="writable" />

#### Writable / Not Writable

```php
<?php

use Symfony\Component\Filesystem\Filesystem;

$filesystem = new Filesystem();

$filesystem->touch('/tmp/test.txt');        // /tmp/test.txt is writable
$filesystem->chmod('/tmp/test.txt', 0444);  // /tmp/test.txt isn't writable
$filesystem->remove('/tmp/test.txt');       // /tmp/test.txt doesn't exist
```
