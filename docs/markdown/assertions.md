# Unify Documentation - Assertions

[&laquo; Back](unify.md)

## Table of Contents

1. [Arrays](#arrays)
  * [Array Has Key](#array-has-key)
2. [Classes](#classes)
  * [Class Has Attribute](#class-has-attribute)
  
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

<a name="class-has-attribute" />

#### Class Has Attribute
