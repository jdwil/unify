<?php
declare(strict_types=1);

namespace JDWil\Unify\Trace;

class Trace
{
    /**
     * @var Assignment[]
     */
    private $assignments;
    private $functionCalls;

    public function __construct()
    {
        $this->assignments = [];
        $this->functionCalls = [];
    }

    public function addAssignment(Assignment $assignment)
    {
        $this->assignments[] = $assignment;
    }

    public function addFunctionCall(FunctionCall $functionCall)
    {
        $this->functionCalls[] = $functionCall;
    }

    public function findAssignment(string $variable, int $line)
    {
        foreach ($this->assignments as $assignment) {
            if ($assignment->getVariable() === $variable &&
                $assignment->getLine() === $line
            ) {
                return $assignment;
            }
        }
        var_dump($variable);
        var_dump($line);
        var_dump($this->assignments);

        return null;
    }
}
