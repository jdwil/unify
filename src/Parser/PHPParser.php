<?php

namespace JDWil\Unify\Parser;

use JDWil\Unify\Assertion\AssertionQueue;
use JDWil\Unify\Assertion\Context;
use JDWil\Unify\Assertion\Pipeline;

class PHPParser
{
    private $assertions;

    private $lines;

    private $line;

    private $lineNumber;

    private $pipeline;

    /**
     * @var Context
     */
    private $context;

    public function __construct($filePath, Pipeline $pipeline)
    {
        $this->context = new Context();
        $this->context->setFile($filePath);
        $this->assertions = new AssertionQueue();
        $this->pipeline = $pipeline;
    }

    public function parse()
    {
        // @todo use filesystem
        $this->lines = file($this->context->getFile());

        // @todo use chain of responsibility. Create a pipeline where parsers can be registered.
        for ($endLine = count($this->lines), $this->lineNumber = 0; $this->lineNumber < $endLine; $this->lineNumber++) {
            $this->line = trim($this->lines[$this->lineNumber]);
            $this->processSingleLineComment();
        }
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
            $this->assertions->add($assertion);
        }
    }

    protected function getAssignedVariableFromNextLine()
    {
        $line = trim($this->lines[$this->lineNumber + 1]);
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
                strpos($line, '#') === 0 ||
                !preg_match('/;$/', $line)
            ) {
                continue;
            }

            return $i + 1;
        }

        return 0;
    }
}
