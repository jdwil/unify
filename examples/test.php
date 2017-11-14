<?php

$b = new Bar(0);
$b->getValue(
    1
); // is 1

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
