<?php

namespace JDWil\Unify\Parser;

use JDWil\Unify\Assertion\Pipeline;

/**
 * Class ParserFactory
 */
class ParserFactory
{
    /**
     * @var FileTypeChecker
     */
    private $fileTypeChecker;

    /**
     * @var Pipeline
     */
    private $pipeline;

    /**
     * ParserFactory constructor.
     * @param FileTypeChecker $fileTypeChecker
     * @param Pipeline $pipeline
     */
    public function __construct(FileTypeChecker $fileTypeChecker, Pipeline $pipeline)
    {
        $this->fileTypeChecker = $fileTypeChecker;
        $this->pipeline = $pipeline;
    }

    /**
     * @param $filePath
     * @return mixed
     */
    public function createParser($filePath)
    {
        $type = $this->fileTypeChecker->determineType($filePath);

        switch ($type) {
            case FileTypeChecker::PHP:
                return new PHPParser($filePath, $this->pipeline);
        }
    }
}
