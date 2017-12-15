<?php
/**
 * Copyright (c) 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details. You should have received a copy of the GNU Lesser General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\Parser;

use JDWil\Unify\Assertion\Php\PhpAssertionInterface;
use JDWil\Unify\Assertion\Php\PhpAssertionQueue;
use JDWil\Unify\ValueObject\PhpContext;
use JDWil\Unify\Assertion\Php\PHPAssertionPipeline;
use JDWil\Unify\Parser\Unify\Php\PhpUnifyParserPipeline;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Php\PhpTestPlan;
use JDWil\Unify\ValueObject\LineRange;

/**
 * Class PHPParser
 */
class PhpParser
{
    /**
     * @var PhpTokenizer
     */
    private $tokenizer;

    /**
     * @var PhpAssertionQueue
     */
    private $assertions;

    /**
     * @var CommandInterface[]
     */
    private $commands;

    /**
     * @var PhpUnifyParserPipeline
     */
    private $assertionPipeline;

    /**
     * @var PhpContext
     */
    private $context;

    /**
     * @var ParserFactory
     */
    private $factory;

    /**
     * PHPParser constructor.
     * @param string $filePath
     * @param ParserFactory $factory
     * @param PhpUnifyParserPipeline $pipeline
     * @param string $autoloadPath
     */
    public function __construct($filePath, ParserFactory $factory, PhpUnifyParserPipeline $pipeline, $autoloadPath)
    {
        $this->factory = $factory;
        $this->context = new PhpContext();
        $this->context->setFile($filePath);
        $this->context->setAutoloadPath($autoloadPath);
        $this->assertions = new PhpAssertionQueue();
        $this->assertionPipeline = $pipeline;
        $this->commands = [];
    }

    /**
     * @param string|null $code
     * @throws \Exception
     */
    public function parse($code = null)
    {
        if (null === $code || file_exists($code)) {
            $this->tokenizer = new PhpTokenizer(file_get_contents($this->context->getFile()));
        } else {
            $this->tokenizer = new PhpTokenizer($code);
        }

        $lastBreakableLine = $line = 0;

        while ($token = $this->tokenizer->next()) {

            if (is_array($token) && $token[2] !== $line) {
                $line = $token[2];
                if ($this->tokenizer->lineIsBreakable($line)) {
                    $lastBreakableLine = $line;
                }
            }

            if ($this->tokenizer->isComment($token)) {
                $singleLineComment = $this->tokenizer->isSingleLineComment($token);
                if ($singleLineComment) {
                    if ($this->tokenizer->nextLineIsBlank()) {
                        $breakableLine = $lastBreakableLine;
                    } else {
                        $breakableLine = $this->tokenizer->getNextBreakableLine($token[2]);
                    }
                } else {
                    $breakableLine = $token[2];
                }
                $breakableLine = $this->tokenizer->toLineRange($breakableLine);

                $this->context->setLine($breakableLine);
                $comment = $this->normalizeComment($token);
                $parser = $this->factory->createUnifyParser();

                if ($assertionTokenGroups = $parser->parse($comment)) {
                    $this->context->setCodeContext(
                        $this->tokenizer->getCodeOnLine($token[2])
                    );
                    foreach ($assertionTokenGroups as $assertionTokenGroup) {
                        $this->assertionPipeline->setContext($this->context);
                        if ($results = $this->assertionPipeline->handle($assertionTokenGroup)) {
                            foreach ($results as $result) {

                                if ($result instanceof PhpAssertionInterface) {
                                    $result->setContext($this->buildContext());
                                    $this->assertions->add($result);
                                } else if ($result instanceof CommandInterface) {
                                    $this->commands[] = $result;
                                }

                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getTestPlans()
    {
        return [
            new PhpTestPlan($this->context->getFile(), '', $this->assertions, $this->commands)
        ];
    }

    /**
     * @return PhpAssertionQueue
     */
    public function getAssertions()
    {
        return $this->assertions;
    }

    /**
     * @return CommandInterface[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @return PhpContext
     */
    protected function buildContext()
    {
        $ret = new PhpContext();
        $ret->setFile($this->context->getFile());
        $ret->setLine($this->context->getLine());

        return $ret;
    }

    /**
     * @param $comment
     * @return array|string
     */
    protected function normalizeComment($comment)
    {
        $lines = explode("\n", $comment[1]);

        array_walk($lines, function (&$line) {
            $line = trim($line);
            $line = preg_replace('~\*/$~', '', $line);
            $line = preg_replace('~^//|/\*\*|/\*|#|\*~', '', $line, 1);
            //$line = trim($line);
            $line = preg_replace('/^\s/', '', $line);
        });

        //$lines = array_values(array_filter($lines));

        for ($i = 0; $i < count($lines); $i++) {
            if (isset($lines[$i], $lines[$i + 1]) && substr($lines[$i], -1) === '\\') {
                $lines[$i] = preg_replace('~\\\\$~', '', $lines[$i]);
                $lines[$i] .= $lines[$i + 1];
                unset($lines[$i + 1]);
                $i--;
                $lines = array_values($lines);
            }
        }

        //$comment = array_filter($lines);
        $comment = implode("\n", $lines);
        //var_dump($comment);

        return $comment;
    }
}
