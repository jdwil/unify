## FileTypeChecker

This class is used to check file types.

_Example_

```php
<?php

use JDWil\Unify\Parser\FileTypeChecker;
use Symfony\Component\Filesystem\Filesystem;

$fileTypeChecker = new FileTypeChecker();
$filesystem = new Filesystem();

file_put_contents('/tmp/test-file.php', '<?php echo $x = 1;');  // Create file /tmp/test-file.php
$type = $fileTypeChecker->determineType('/tmp/test-file.php');  // $type is FileTypeChecker::PHP
$filesystem->remove('/tmp/test-file.php');                      // Delete file /tmp/test-file.php
```