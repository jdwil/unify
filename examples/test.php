<?php

// $x == 1
$x = 1;

// 0
$y = $x % 1;

// $z = 2
$z = foo($x); // $z = 2

$bar = new Bar($x);
$blerg = $bar->getValue($z);

echo "HERE I GO AGAIN\n";

// 5
$h = 5;

exit(0);

function foo(int $a) {
    return $a * 2;
}

class Bar
{
    private $bar;

    public function __construct($bar)
    {
        $this->bar = $bar;
    }

    public function getValue($b)
    {
        return $b + $this->bar;
    }
}
