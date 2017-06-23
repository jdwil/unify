<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

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
