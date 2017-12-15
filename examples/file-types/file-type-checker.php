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

use JDWil\Unify\Parser\FileTypeChecker;
use Symfony\Component\Filesystem\Filesystem;

$fileTypeChecker = new FileTypeChecker();
$filesystem = new Filesystem();

/**
 * Test that PHP files are detected properly.
 */

file_put_contents('/tmp/test.php', '<?php $x = 1;'); // creates file /tmp/test.php

/**
 * /tmp/test.php now contains:
 * """
 * <?php $x = 1;
 * """
 */

// $type is 0 (FileTypeChecker::PHP)
$type = $fileTypeChecker->determineType('/tmp/test.php');

for ($i = 0; $i <= 4; $i++) {
    $x = $i; // $x is 0, 1, 2, 3, 4
}

// deletes file /tmp/test.php
$filesystem->remove('/tmp/test.php');

// Avoid false-positive on last assertion with exit()
exit(0);
