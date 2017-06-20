<?php
declare(strict_types=1);

namespace JDWil\Unify\Assertion;

class Context
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $assignmentVariable;

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getAssignmentVariable()
    {
        return $this->assignmentVariable;
    }

    /**
     * @param string $assignmentVariable
     */
    public function setAssignmentVariable($assignmentVariable)
    {
        $this->assignmentVariable = $assignmentVariable;
    }
}
