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
     * @var Context
     */
    private $context;

    /**
     * @var int
     */
    private $lastAssertionLine;

    public function __construct($filePath, Pipeline $pipeline)
    {
        $this->filePath = $filePath;
        $this->lastAssertionLine = 0;
        $this->context = new Context();
        $this->context->setFile($filePath);
        $this->assertions = new AssertionQueue();
        $this->pipeline = $pipeline;
    }

    public function parse($code = null)
    {
        if (null === $code) {
            // @todo use finder
            $this->lines = file($this->context->getFile());
        } else {
            $this->lines = explode("\n", $code);
            array_walk($this->lines, function (&$line, $index) {
                $line = sprintf("%s\n", $line);
            });
        }

        for ($endLine = count($this->lines), $this->lineNumber = 0; $this->lineNumber < $endLine; $this->lineNumber++) {
            $this->line = trim($this->lines[$this->lineNumber]);
            $this->collectUseStatements();
            $this->processSingleLineComment();
            $this->processInlineComment();
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

        if ($assertion = $this->pipeline->handleLine($this->line, $this->context)) {
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

        if ($assertion = $this->pipeline->handleLine($this->line, $this->context)) {
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
