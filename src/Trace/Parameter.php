<?php
declare(strict_types=1);

namespace JDWil\Unify\Trace;

class Parameter
{
    private $variable;
    private $value;

    public function __construct(string $variable, $value)
    {
        $this->variable = $variable;
        $this->value = $value;
    }
}
