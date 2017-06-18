<?php
declare(strict_types=1);

namespace JDWil\Unify\Parser;

class ParserFactory
{
    private $fileTypeChecker;

    public function __construct(FileTypeChecker $fileTypeChecker)
    {
        $this->fileTypeChecker = $fileTypeChecker;
    }

    public function createParser(string $filePath)
    {
        $type = $this->fileTypeChecker->determineType($filePath);

        switch ($type) {
            case FileTypeChecker::PHP:
                return new PHPParser($filePath);
        }
    }
}
