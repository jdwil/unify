<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

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

function foo($a) {
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
