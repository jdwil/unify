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
     * @var string
     */
    private $autoloadPath;

    /**
     * ParserFactory constructor.
     * @param FileTypeChecker $fileTypeChecker
     * @param Pipeline $pipeline
     * @param string $autoloadPath
     */
    public function __construct(FileTypeChecker $fileTypeChecker, Pipeline $pipeline, $autoloadPath)
    {
        $this->fileTypeChecker = $fileTypeChecker;
        $this->pipeline = $pipeline;
        $this->autoloadPath = $autoloadPath;
    }

    /**
     * @param $filePath
     * @return PHPParser
     */
    public function createPhpParser($filePath)
    {
        return new PHPParser($filePath, $this->pipeline);
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

            case FileTypeChecker::MARKDOWN:
                return new MarkdownParser($filePath, $this, $this->autoloadPath);
        }
    }
}
