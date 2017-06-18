<?php
declare(strict_types=1);

namespace JDWil\Unify\Parser;

use JDWil\Unify\TestRunner\Assertion;

class PHPParser
{
    private $filePath;

    private $assertions;

    private $lines;

    private $line;

    private $lineNumber;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->assertions = [];
    }

    public function parse()
    {
        // @todo use filesystem
        $this->lines = file($this->filePath);

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
        if (!preg_match('#^//#', $this->line) &&
            !preg_match('#^/\*.*\*/$#', $this->line)) {
            return false;
        }

        $this->line = trim(str_replace(['//', '/*', '*/'], '', $this->line));

        /**
         * Single value in comments will be asserted as equal, ie:
         *
         * // 'bar'
         * $foo = 'bar';
         */
        if (preg_match('/^[\'\"]?[a-zA-Z0-9_\.]+[\'\"]?$/', $this->line, $m)) {
            $this->assertions[] = Assertion::toCheckEquality(
                $this->getAssignedVariableFromNextLine(),
                $m[0],
                $this->nextStatementLine($this->lineNumber + 2),
                $this->filePath
            );
        }

        /**
         * Assertions can be formatted as simple expressions:
         *
         * // $foo = 'bar'
         * // $foo == 'bar'
         * // $foo === 'bar'
         */
        if (preg_match('/(\$[a-zA-Z]\w*)\s*([=><]=?=?)\s*([\'\"]?\w[\'\"]?)/', $this->line, $m)) {
            if (in_array($m[2], ['=', '==', '==='], true)) {
                $this->assertions[] = Assertion::toCheckEquality($m[1], $m[3], $this->nextStatementLine($this->lineNumber + 2), $this->filePath);
            }
            // @todo handle >, <, >=, <=
        }
    }

    protected function getAssignedVariableFromNextLine()
    {
        $line = trim($this->lines[$this->lineNumber + 1]);
        preg_match('/(\$[a-zA-Z]\w*)\s*=[^=]/', $line, $m);

        return $m[1];
    }

    protected function nextStatementLine(int $start)
    {
        for ($max = count($this->lines), $i = $start; $i < $max; $i++) {
            $line = trim($this->lines[$i]);

            if (empty($line) ||
                strpos($line, '//') === 0 ||
                strpos($line, '*') === 0 ||
                strpos($line, '/*') === 0
            ) {
                continue;
            }

            return $i + 1;
        }
    }
}
