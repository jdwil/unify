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

use JDWil\Unify\Assertion\PHP\PHPAssertionInterface;
use JDWil\Unify\Assertion\PHP\PHPAssertionQueue;
use JDWil\Unify\ValueObject\PHPContext;
use JDWil\Unify\Assertion\PHP\PHPAssertionPipeline;
use JDWil\Unify\Parser\Unify\PHP\PHPUnifyParserPipeline;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\PHP\PHPTestPlan;
use JDWil\Unify\ValueObject\LineRange;

/**
 * Class PHPParser
 */
class PHPParser
{
    /**
     * @var PHPAssertionQueue
     */
    private $assertions;

    /**
     * @var CommandInterface[]
     */
    private $commands;

    /**
     * @var array
     */
    private $lines;

    /**
     * @var PHPUnifyParserPipeline
     */
    private $assertionPipeline;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var int
     */
    private $index;

    /**
     * @var PHPContext
     */
    private $context;

    /**
     * @var int
     */
    private $lastAssertionLine;

    /**
     * @var ParserFactory
     */
    private $factory;

    /**
     * PHPParser constructor.
     * @param string $filePath
     * @param ParserFactory $factory
     * @param PHPUnifyParserPipeline $pipeline
     * @param string $autoloadPath
     */
    public function __construct($filePath, ParserFactory $factory, PHPUnifyParserPipeline $pipeline, $autoloadPath)
    {
        $this->filePath = $filePath;
        $this->lastAssertionLine = 0;
        $this->factory = $factory;
        $this->context = new PHPContext();
        $this->context->setFile($filePath);
        $this->context->setAutoloadPath($autoloadPath);
        $this->assertions = new PHPAssertionQueue();
        $this->assertionPipeline = $pipeline;
        $this->commands = [];
    }

    /**
     * @param string|null $code
     * @throws \Exception
     */
    public function parse($code = null)
    {
        if (null === $code) {
            // @todo use finder
            $this->lines = file($this->context->getFile());
            array_unshift($this->lines, '');
            $this->tokens = token_get_all(file_get_contents($this->context->getFile()));
        } else {
            $this->lines = explode("\n", $code);
            array_unshift($this->lines, '');
            $this->tokens = token_get_all($code);
            array_walk($this->lines, function (&$line) {
                $line = sprintf("%s\n", $line);
            });
        }

        $this->index = 1;
        $lastBreakableLine = $line = 0;

        while ($token = $this->next()) {

            if (is_array($token) && $token[2] !== $line) {
                $line = $token[2];
                if ($this->lineIsBreakable($line)) {
                    $lastBreakableLine = $line;
                }
            }

            if ($this->isComment($token)) {
                $singleLineComment = $this->isSingleLineComment($token);
                if ($singleLineComment) {
                    if ($this->nextLineIsBlank()) {
                        $breakableLine = $lastBreakableLine;
                    } else {
                        $breakableLine = $this->getNextBreakableLine($token[2]);
                    }
                } else {
                    $breakableLine = $token[2];
                }
                $breakableLine = $this->toLineRange($breakableLine);

                $this->context->setLine($breakableLine);
                $comment = $this->normalizeComment($token);
                $parser = $this->factory->createUnifyParser();

                if ($assertionTokenGroups = $parser->parse($comment)) {
                    foreach ($assertionTokenGroups as $assertionTokenGroup) {
                        $this->assertionPipeline->setContext($this->context);
                        if ($results = $this->assertionPipeline->handle($assertionTokenGroup)) {
                            foreach ($results as $result) {

                                if ($result instanceof PHPAssertionInterface) {
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
            new PHPTestPlan($this->filePath, '', $this->assertions, $this->commands)
        ];
    }

    /**
     * @return PHPAssertionQueue
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
     * @param $line
     * @return LineRange
     */
    protected function toLineRange($line)
    {
        $start = $this->index;
        $this->seekLine($line);
        $end = $line;

        while ($token = $this->next()) {
            if (!is_array($token) && $token === ';') {
                break;
            }

            if (is_array($token)) {
                $end = $token[2];
            }
        }

        $this->index = $start;

        return new LineRange($line, $end);
    }

    /**
     * @return PHPContext
     */
    protected function buildContext()
    {
        $ret = new PHPContext();
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
            $line = preg_replace('~^//|/\*\*|/\*|#|\*~', '', $line);
            $line = trim($line);
        });


        $lines = array_values(array_filter($lines));

        for ($i = 0; $i < count($lines); $i++) {
            if (isset($lines[$i], $lines[$i + 1]) && substr($lines[$i], -1) === '\\') {
                $lines[$i] = preg_replace('~\\\\$~', '', $lines[$i]);
                $lines[$i] .= $lines[$i + 1];
                unset($lines[$i + 1]);
                $i--;
                $lines = array_values($lines);
            }
        }

        $comment = array_filter($lines);
        $comment = implode("\n", $comment);

        return $comment;
    }

    /**
     * @param $comment
     * @return bool
     */
    protected function isSingleLineComment($comment)
    {
        $line = $comment[2];
        $tokens = $this->getLineTokens($line);
        foreach ($tokens as $token) {
            if ($this->isBreakableToken($token)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function nextLineIsBlank()
    {
        $token = $this->tokens[$this->index];

        return $this->isWhitespace($token) && substr_count($token[1], "\n") > 1;
    }

    /**
     * @param int $line
     * @return bool
     */
    protected function lineIsBreakable($line)
    {
        $tokens = $this->getLineTokens($line);
        foreach ($tokens as $token) {
            if ($this->isBreakableToken($token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $onOrAfterLine
     * @return false|int
     */
    protected function getNextBreakableLine($onOrAfterLine)
    {
        $i = 1;
        do {
            $peek = $this->peek($i);
            $i++;
        } while (
            ($peek && !$this->isBreakableToken($peek)) ||
            ($peek && (!is_array($peek) || $peek[2] < $onOrAfterLine))
        );

        return $this->isBreakableToken($peek) ? $peek[2] : false;
    }

    /**
     * @return string|false
     */
    protected function getNextAssignedVariable()
    {
        $i = 1;
        do {
            $peek1 = $this->peek($i);
            $peek2 = $this->peek($i + 1);
            $i++;
        } while ($peek1 && $peek2 && !$this->isVariable($peek1) && $peek2 !== '=');

        return $this->isVariable($peek1) ? $peek1[1] : false;
    }

    /**
     * @param int $line
     */
    protected function seekLine($line)
    {
        foreach ($this->tokens as $index => $token) {
            if (is_array($token) && $token[2] >= $line) {
                $this->index = $index;
                return;
            }
        }
    }

    /**
     * @param array|string $token
     * @return bool
     */
    protected function isWhitespace($token)
    {
        return is_array($token) && $token[0] === T_WHITESPACE;
    }

    /**
     * @param array|string $token
     * @return bool
     */
    protected function isComment($token)
    {
        return is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true);
    }

    /**
     * @param array|string $token
     * @return bool
     */
    protected function isVariable($token)
    {
        return is_array($token) && $token[0] === T_VARIABLE;
    }

    /**
     * @param int $line
     * @return array
     */
    protected function getLineTokens($line)
    {
        $ret = [];

        foreach ($this->tokens as $token) {
            if (is_array($token) && $token[2] === $line) {
                $ret[] = $token;
            }
        }

        return $ret;
    }

    /**
     * @param array|string $token
     * @return bool
     */
    protected function isBreakableToken($token)
    {
        if (!is_array($token) || in_array($token[0], [
            T_COMMENT, T_DOC_COMMENT, T_WHITESPACE
        ], true)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool|array
     */
    protected function next()
    {
        if (!isset($this->tokens[$this->index])) {
            return false;
        }

        return $this->tokens[$this->index++];
    }

    /**
     * @param int $advance
     * @return bool|array
     */
    protected function peek($advance = 1)
    {
        $i = 1;
        $token = false;
        $hits = 0;

        while ($hits < $advance) {
            if (isset($this->tokens[$this->index + $i])) {
                $token = $this->tokens[$this->index + $i];
                if (!$this->isWhitespace($token)) {
                    $hits++;
                }
                $i++;
            } else {
                return false;
            }
        }

        return $token;
    }
}
