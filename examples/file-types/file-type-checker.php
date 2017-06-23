<?php

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

for ($i = 0; $i <= 3; $i++) {
    $x = $i; // $x is 0, 1, 2, 3
}

// deletes file /tmp/test.php
$filesystem->remove('/tmp/test.php');

// Always use exit() in our example PHP files.
exit(0);
