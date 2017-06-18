<?php
declare(strict_types=1);

namespace JDWil\Unify\Trace;

class FunctionCall
{
    private $functionName;
    private $file;
    private $line;
    private $parameters;
    private $return;

    public function __construct(string $functionName, string $file, int $line, array $parameters = [], $return = null)
    {
        $this->functionName = $functionName;
        $this->file = $file;
        $this->line = $line;
        $this->parameters = $parameters;
        $this->return = $return;
    }

    public function setReturn($value)
    {
        $this->return = $value;
    }
}
