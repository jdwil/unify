<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use JDWil\Unify\Parser\FileTypeChecker;

$fileTypeChecker = new FileTypeChecker();

/**
 * Test that PHP files are detected properly.
 */

// creates file /tmp/test.php
file_put_contents('/tmp/test.php', '<?php $x = 1;');

/**
 * /tmp/test.php now contains:
 * """
 * <?php $x = 1;
 * """
 */

// $type is FileTypeChecker::PHP which is 0
$type = $fileTypeChecker->determineType('/tmp/test.php');

// deletes file /tmp/test.php
unlink('/tmp/test.php');

