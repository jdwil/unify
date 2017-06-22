<?php

namespace JDWil\Unify\Parser;

use JDWil\Unify\Assertion\Pipeline;
use Phlexy\Lexer\Stateful;

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
     * @var Stateful
     */
    private $lexer;

    /**
     * ParserFactory constructor.
     * @param FileTypeChecker $fileTypeChecker
     * @param Stateful $lexer
     * @param Pipeline $pipeline
     * @param string $autoloadPath
     */
    public function __construct(FileTypeChecker $fileTypeChecker, Stateful $lexer, Pipeline $pipeline, $autoloadPath)
    {
        $this->fileTypeChecker = $fileTypeChecker;
        $this->lexer = $lexer;
        $this->pipeline = $pipeline;
        $this->autoloadPath = $autoloadPath;
    }

    /**
     * @param $filePath
     * @return PHPParser
     */
    public function createPhpParser($filePath)
    {
        return new PHPParser($filePath, $this, $this->pipeline, $this->autoloadPath);
    }

    /**
     * @return UnifyParser
     */
    public function createUnifyParser()
    {
        return new UnifyParser($this->lexer);
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
                return new PHPParser($filePath, $this, $this->pipeline, $this->autoloadPath);

            case FileTypeChecker::MARKDOWN:
                return new MarkdownParser($filePath, $this, $this->autoloadPath);
        }
    }
}
