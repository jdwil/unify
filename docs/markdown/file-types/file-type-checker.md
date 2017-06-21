## FileTypeChecker

This class is used to check file types.

_Example_

```php
<?php

use JDWil\Unify\Parser\FileTypeChecker;
use Symfony\Component\Filesystem\Filesystem;

$fileTypeChecker = new FileTypeChecker();
$filesystem = new Filesystem();

/*
 * Test
 */

/**
 * PHP
 */
$filesystem->touch('/tmp/test.php');                            // Create file /tmp/test.php
$type = $fileTypeChecker->determineType('/tmp/test-file.php');  // $type is FileTypeChecker::PHP
$filesystem->remove('/tmp/test-file.php');                      // Delete file /tmp/test-file.php

/**
 * Markdown 
 */
foreach (['markdown', 'mdown', 'mkdn', 'md'] as $extension) {
    $file = sprintf('/tmp/test.%s', $extension);
    
    /* Creates /tmp/test.markdown, then /tmp/test.mdown, /tmp/test.mkdn, /tmp/test.md */
    $filesystem->touch($file);                      
    
    $type = $fileTypeChecker->determineType($file); // $type is FileTypeChecker::MARKDOWN
    
    /* Deletes /tmp/test.markdown, /tmp/test.mdown, /tmp/test.mkdn, /tmp/test.md */
    $filesystem->remove($file);                     
}
```
