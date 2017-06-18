<?php
declare(strict_types=1);

namespace JDWil\Unify\Debugger;

class DebugStep
{
    const TYPE_COMMAND = 0;
    const TYPE_GET_VALUE = 1;

    private $type;

    private $command;

    private $variable;

    private $value;

    private function __construct() {}

    public static function toRunCommand(string $command)
    {
        $ret = new DebugStep();
        $ret->type = self::TYPE_COMMAND;
        $ret->command = $command;

        return $ret;
    }

    public static function toGetValue(string $variable)
    {
        $ret = new DebugStep();
        $ret->type = self::TYPE_GET_VALUE;
        $ret->variable = $variable;

        return $ret;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getVariable()
    {
        return $this->variable;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
