<?php
declare(strict_types=1);

namespace JDWil\Unify\Parser;

use JDWil\Unify\Assertion\AssertEqual;
use JDWil\Unify\Assertion\AssertFileExists;
use JDWil\Unify\Assertion\AssertFileNotExists;
use JDWil\Unify\Assertion\AssertionQueue;

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
        $this->assertions = new AssertionQueue();
    }

    public function parse()
    {
        // @todo use filesystem
        $this->lines = file($this->filePath);

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
            $this->assertions->add(new AssertEqual(
                $this->getAssignedVariableFromNextLine(),
                $m[0],
                $this->nextStatementLine($this->lineNumber + 2),
                $this->filePath
            ));
        }

        /**
         * Assertions can be formatted as simple expressions:
         *
         * // $foo = 'bar'
         * // $foo == 'bar'
         * // $foo === 'bar'
         */
        if (preg_match('/(\$[a-zA-Z]\w*)\s*(is|[=><]=?=?)\s*([\'\"]?[a-zA-Z0-9_:>-]*[\'\"]?)/', $this->line, $m)) {
            if (in_array($m[2], ['=', '==', '===', 'is'], true)) {
                $this->assertions->add(new AssertEqual(
                    $m[1],
                    $m[3],
                    $this->nextStatementLine($this->lineNumber + 2),
                    $this->filePath
                ));
            }
            // @todo handle >, <, >=, <=
        }

        /**
         * Assert a file exists
         *
         * // creates file /path/to/file.xyz
         */
        if (preg_match('/creates? file ([^\s]+)/i', $this->line, $m)) {
            $this->assertions->add(new AssertFileExists(
                $m[1],
                $this->nextStatementLine($this->lineNumber + 2),
                $this->filePath
            ));
        }

        /**
         * Assert a file does not exist.
         *
         * // deletes file /path/to/file.xyz
         */
        if (preg_match('/deletes? file ([^\s]+)/i', $this->line, $m)) {
            $this->assertions->add(new AssertFileNotExists(
                $m[1],
                $this->nextStatementLine($this->lineNumber + 2),
                $this->filePath
            ));
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
                strpos($line, '/*') === 0 ||
                !preg_match('/;$/', $line)
            ) {
                continue;
            }

            return $i + 1;
        }

        return 0;
    }
}
