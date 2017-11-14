<?php
/**
 * Copyright (c) 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details. You should have received a copy of the GNU Lesser General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see
 * <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use JDWil\Unify\Parser\PhpTokenizer;
use JDWil\Unify\Exception\AssertionFailedException;

$tokenizer = new PhpTokenizer('<?php

$f = new Foo();
$f->bar(
    2
); // is 1

$x = 1;

$f->bar(
    2
); // is 1

// is Foo
class Foo {
    public function bar($x)
    {
        return 1;
    }
}
');

/**
 * Test toLineRange()
 */

$range = $tokenizer->toLineRange(6);
if ($range->getStart() !== 4 || $range->getEnd() !== 6) {
    throw new AssertionFailedException(
        "toLineRange failed for multi-line statement.\n\nExpected: 4, 6\nActual: {$range->getStart()}, {$range->getEnd()}\n\n"
    );
}

$range = $tokenizer->toLineRange(12);
if ($range->getStart() !== 10 || $range->getEnd() !== 12) {
    throw new AssertionFailedException(
        "toLineRange failed for multi-line statement.\n\nExpected: 10, 12\nActual: {$range->getStart()}, {$range->getEnd()}\n\n"
    );
}

$range = $tokenizer->toLineRange(3);
if ($range->getStart() !== 3 || $range->getEnd() !== 3) {
    throw new AssertionFailedException(
        "toLineRange failed for single-line statement.\n\nExpected: 3, 3\nActual: {$range->getStart()}, {$range->getEnd()}\n\n"
    );
}

$range = $tokenizer->toLineRange(8);
if ($range->getStart() !== 8 || $range->getEnd() !== 8) {
    throw new AssertionFailedException(
        "toLineRange failed for single-line statement.\n\nExpected: 8, 8\nActual: {$range->getStart()}, {$range->getEnd()}\n\n"
    );
}

/**
 * Test getCodeOnLine()
 */

$code = $tokenizer->getCodeOnLine(6);
$expected = '$f->bar(
    2
)';

if ($code !== $expected) {
    throw new AssertionFailedException("getCodeOnLine(6) failed.\n\nExpected: $expected\nActual: $code\n\n");
}

$code = $tokenizer->getCodeOnLine(3);
$expected = '$f = new Foo()';

if ($code !== $expected) {
    throw new AssertionFailedException("getCodeOnLine(3) failed.\n\nExpected: $expected\nActual: $code\n\n");
}

/**
 * Test isSingleLineComment()
 */

$tokenizer->reset();
while ($token = $tokenizer->next()) {
    if ($tokenizer->isComment($token)) {
        break;
    }
}

$result = $tokenizer->isSingleLineComment($token);
if ($result !== false) {
    throw new AssertionFailedException("isSingleLineComment() failed.\n\nExpected: false\nActual: $result\n\n");
}

while ($token = $tokenizer->next()) {
    if ($tokenizer->isComment($token)) {
        break;
    }
}

$result = $tokenizer->isSingleLineComment($token);
if ($result !== false) {
    throw new AssertionFailedException("isSingleLineComment() failed.\n\nExpected: false\nActual: $result\n\n");
}

while ($token = $tokenizer->next()) {
    if ($tokenizer->isComment($token)) {
        break;
    }
}

$result = $tokenizer->isSingleLineComment($token);
if ($result !== true) {
    throw new AssertionFailedException("isSingleLineComment() failed.\n\nExpected: true\nActual: $result\n\n");
}

// @todo finish tests.
