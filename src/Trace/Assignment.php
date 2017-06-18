<?php
declare(strict_types=1);

namespace JDWil\Unify\Trace;

class Assignment
{
    private $variable;
    private $value;
    private $file;
    private $line;

    public function __construct(string $variable, $value, string $file, int $line)
    {
        $this->variable = $variable;
        $this->value = $value;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getVariable(): string
    {
        return $this->variable;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }
}
