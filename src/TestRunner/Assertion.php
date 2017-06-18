<?php
declare(strict_types=1);

namespace JDWil\Unify\TestRunner;

class Assertion
{
    const TYPE_EQUALS = 0;

    private $line;
    private $type;
    private $left;
    private $right;
    private $file;

    private function __construct() {}

    public static function toCheckEquality(string $variable, $value, int $line, string $file)
    {
        $ret = new Assertion();
        $ret->type = self::TYPE_EQUALS;
        $ret->left = $variable;
        $ret->right = $value;
        $ret->line = $line;
        $ret->file = $file;

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return mixed
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }
}
