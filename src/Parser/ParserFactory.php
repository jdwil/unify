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

use JDWil\Unify\Assertion\PHP\PHPAssertionPipeline;
use Phlexy\Lexer\Stateful;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class ParserFactory
 */
class ParserFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * ParserFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $filePath
     * @return PHPParser
     * @throws \Exception
     */
    public function createPhpParser($filePath)
    {
        /** @var PHPAssertionPipeline $pipeline */
        $pipeline = $this->container->get('php_assertion_pipeline');
        return new PHPParser(
            $filePath,
            $this,
            $pipeline,
            $this->container->getParameter('autoload_path')
        );
    }

    public function createMarkdownParser()
    {
        /** @var Stateful $lexer */
        $lexer = $this->container->get('markdown_lexer');

        return new MarkdownParser($lexer, $this, $this->container->getParameter('autoload_path'));
    }

    public function createShellParser($filePath)
    {
        /** @var Stateful $lexer */
        $lexer = $this->container->get('shell_lexer');

        return new ShellParser($filePath, $lexer);
    }

    /**
     * @return UnifyParser
     * @throws \Exception
     */
    public function createUnifyParser()
    {
        /** @var Stateful $lexer */
        $lexer = $this->container->get('unify_lexer');

        return new UnifyParser($lexer);
    }

    /**
     * @param $filePath
     * @return mixed
     * @throws \Exception
     */
    public function createParser($filePath)
    {
        /** @var FileTypeChecker $fileTypeChecker */
        $fileTypeChecker = $this->container->get('file_type_checker');
        $type = $fileTypeChecker->determineType($filePath);

        $autoloadPath = $this->container->getParameter('autoload_path');
        switch ($type) {
            case FileTypeChecker::PHP:
                /** @var PHPAssertionPipeline $pipeline */
                $pipeline = $this->container->get('php_assertion_pipeline');
                return new PHPParser($filePath, $this, $pipeline, $autoloadPath);

            case FileTypeChecker::MARKDOWN:
                /** @var Stateful $lexer */
                $lexer = $this->container->get('markdown_lexer');

                return new MarkdownParser($lexer, $this, $autoloadPath);
        }
    }
}
