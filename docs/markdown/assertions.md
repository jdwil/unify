# Unify Documentation - Assertions

[&laquo; Back](unify.md)

## Table of Contents

1. [Arrays](#arrays)
  * [Array Has Key](#array-has-key)
2. [Classes](#classes)
  * [Class Has Property](#class-has-property)
  * [Class Doesn't Have Property](#class-lacks-property)
  
<a name="arrays" />

## Arrays

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
