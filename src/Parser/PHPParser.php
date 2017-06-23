<?php

namespace JDWil\Unify\Parser;

use JDWil\Unify\Assertion\AssertionQueue;
use JDWil\Unify\Assertion\Context;
use JDWil\Unify\Assertion\Pipeline;
use JDWil\Unify\TestRunner\TestPlan;

class PHPParser
{
    private $assertions;

    private $lines;

    private $line;

    private $lineNumber;

    private $pipeline;

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
     * @var Context
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

    public function __construct($filePath, ParserFactory $factory, Pipeline $pipeline, $autoloadPath)
    {
        $this->filePath = $filePath;
        $this->lastAssertionLine = 0;
        $this->factory = $factory;
        $this->context = new Context();
        $this->context->setFile($filePath);
        $this->context->setAutoloadPath($autoloadPath);
        $this->assertions = new AssertionQueue();
        $this->pipeline = $pipeline;
    }

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
            array_walk($this->lines, function (&$line, $index) {
                $line = sprintf("%s\n", $line);
            });
        }

        //$this->stripWhitespace();
        $this->index = 1;

        while ($token = $this->next()) {
            if ($this->isComment($token)) {
                $singleLineComment = $this->isSingleLineComment($token);
                if ($singleLineComment) {
                    $breakableLine = $this->getNextBreakableLine($token[2]);
                } else {
                    $breakableLine = $token[2];
                }

                $this->context->setLine($breakableLine);
                $comment = $this->normalizeComment($token);
                $parser = $this->factory->createUnifyParser();
                if ($assertionTokenGroups = $parser->parse($comment)) {
                    foreach ($assertionTokenGroups as $assertionTokenGroup) {
                        if ($assertions = $this->pipeline->handleComment($assertionTokenGroup, $this->context)) {
                            foreach ($assertions as $assertion) {
                                $assertion->setCodeContext(
                                    implode('', array_slice(
                                        $this->lines,
                                        $this->lastAssertionLine,
                                        $token[2] + 1 - $this->lastAssertionLine
                                    ))
                                );
                                $this->lastAssertionLine = $token[2];
                                $assertion->setContext($this->context);
                                $this->context->resetCodeContext();
                                $this->assertions->add($assertion);
                            }
                        }
                    }
                }
            }
        }
    }

    public function getTestPlans()
    {
        return [
            new TestPlan($this->filePath, $this->assertions)
        ];
    }

    public function getAssertions()
    {
        return $this->assertions;
    }

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

    protected function stripWhitespace()
    {
        $tokens = [];
        foreach ($this->tokens as $token) {
            if (is_array($token) && !$this->isWhitespace($token)) {
                $tokens[] = $token;
            }
        }

        $this->tokens = $tokens;
    }

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

    protected function seekLine($line)
    {
        foreach ($this->tokens as $index => $token) {
            if (is_array($token) && $token[2] >= $line) {
                $this->index = $line;
                return;
            }
        }
    }

    protected function isWhitespace($token)
    {
        return is_array($token) && $token[0] === T_WHITESPACE;
    }

    protected function isComment($token)
    {
        return is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true);
    }

    protected function isVariable($token)
    {
        return is_array($token) && $token[0] === T_VARIABLE;
    }

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

    protected function isBreakableToken($token)
    {
        if (!is_array($token) || in_array($token[0], [
            T_COMMENT, T_DOC_COMMENT, T_WHITESPACE
        ], true)) {
            return false;
        }

        return true;
    }

    protected function next()
    {
        if (!isset($this->tokens[$this->index])) {
            return false;
        }

        return $this->tokens[$this->index++];
    }

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
