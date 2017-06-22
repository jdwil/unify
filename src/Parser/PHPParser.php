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

    public function __construct($filePath, Pipeline $pipeline, $autoloadPath)
    {
        $this->filePath = $filePath;
        $this->lastAssertionLine = 0;
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

        $this->index = 1;

        while ($token = $this->next()) {
            if ($this->isComment($token)) {
                list($type, $comment, $lineNumber) = $token;
                $comment = $this->normalizeComment($comment);
                $this->context->setLine($this->getNextBreakableLine());
                $this->context->setAssignmentVariable($this->getNextAssignedVariable());
                $this->context->setCodeContext(''); // @todo add code context

                if ($assertions = $this->pipeline->handleComment($comment, $this->context)) {
                    foreach ($assertions as $assertion) {
                        $assertion->setCodeContext(
                            implode('', array_slice(
                                $this->lines,
                                $this->lastAssertionLine,
                                $this->lineNumber + 2 - $this->lastAssertionLine
                            ))
                        );
                        $assertion->setContext($this->context);
                        $this->context->resetCodeContext();
                        $this->assertions->add($assertion);
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
        $lines = explode("\n", $comment);
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

        return array_filter($lines);
    }

    protected function getNextBreakableLine()
    {
        $i = 1;
        do {
            $peek = $this->peek($i);
            $i++;
        } while ($peek && !$this->isBreakableToken($peek));

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

    // Old

    protected function processSingleLineComment()
    {
        if (!preg_match('~^//~', $this->line) &&
            !preg_match('~^/\*.*\*/$~', $this->line) &&
            !preg_match('~^#~', $this->line)
        ) {
            return false;
        }

        $this->line = trim(str_replace(['//', '/*', '*/', '#'], '', $this->line));

        $this->context->setAssignmentVariable($this->getAssignedVariableFromNextLine());
        $this->context->setLine($this->nextStatementLine($this->lineNumber + 2));

        if ($assertion = $this->pipeline->handleComment($this->line, $this->context)) {
            $assertion->setCodeContext(
                implode('', array_slice(
                    $this->lines,
                    $this->lastAssertionLine,
                    $this->lineNumber + 2 - $this->lastAssertionLine
                ))
            );
            $assertion->setContext($this->context);
            $this->lastAssertionLine = $this->lineNumber + 2;
            $this->context->resetCodeContext();
            $this->assertions->add($assertion);
        }
    }

    protected function processInlineComment()
    {
        if (preg_match('~.+//(.*)$~', $this->line, $m) ||
            preg_match('~.+#(.*)$~', $this->line, $m) ||
            preg_match('~.+/\*(.*)\*/~U', $this->line, $m)) {
            $this->line = trim($m[1]);
        } else {
            return false;
        }

        $this->context->setAssignmentVariable($this->getAssignedVariableFromLine($this->lineNumber));
        $this->context->setLine($this->nextStatementLine($this->lineNumber + 1));

        if ($assertion = $this->pipeline->handleComment($this->line, $this->context)) {
            $assertion->setCodeContext(
                implode('', array_slice(
                    $this->lines,
                    $this->lastAssertionLine,
                    $this->lineNumber + 1 - $this->lastAssertionLine
                ))
            );
            $assertion->setContext($this->context);
            $this->lastAssertionLine = $this->lineNumber + 1;
            $this->context->resetCodeContext();
            $this->assertions->add($assertion);
        }
    }

    protected function getAssignedVariableFromNextLine()
    {
        return $this->getAssignedVariableFromLine($this->lineNumber + 1);
    }

    protected function getAssignedVariableFromLine($line)
    {
        $line = trim($this->lines[$line]);
        if (!preg_match('/(\$[a-zA-Z]\w*)\s*=[^=]/', $line, $m)) {
            return null;
        }

        return $m[1];
    }

    protected function nextStatementLine($start)
    {
        for ($max = count($this->lines), $i = $start; $i < $max; $i++) {
            $line = trim($this->lines[$i]);

            if (empty($line) ||
                strpos($line, '//') === 0 ||
                strpos($line, '*') === 0 ||
                strpos($line, '/*') === 0 ||
                strpos($line, '#') === 0
            ) {
                continue;
            }

            return $i + 1;
        }

        return 0;
    }

    protected function collectUseStatements()
    {
        if (preg_match('~use [^;]+;~', $this->line, $m)) {
            $this->context->addUseStatement($m[0]);
        }
    }
}
