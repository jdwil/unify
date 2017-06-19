<?php
declare(strict_types=1);

namespace JDWil\Unify\TestRunner;

class Assertion
{
    const TYPE_EQUALS = 0;
    const TYPE_FILE_EXISTS = 1;

    private $line;
    private $type;
    private $left;
    private $right;
    private $file;
    private $result;
    private $filePath;

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

    public static function toCheckFileExists(string $filePath, int $line, string $file)
    {
        $ret = new Assertion();
        $ret->type = self::TYPE_FILE_EXISTS;
        $ret->line = $line;
        $ret->file = $file;
        $ret->filePath = $filePath;

        return $ret;
    }

    public function getDebuggerCommand()
    {
        switch($this->type) {
            case self::TYPE_EQUALS:
                return "context_get -i %d -d 0 -c 0\0";
            case self::TYPE_FILE_EXISTS:
                return sprintf(
                    "eval -i %%d -- %s\0",
                    base64_encode(
                        sprintf('file_exists(\'%s\');', $this->getFilePath())
                    )
                );
        }
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

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function assert(\DOMElement $response)
    {
        switch ($this->type) {
            case self::TYPE_EQUALS:
                foreach ($response->childNodes as $child) {
                    if ($child->getAttribute('name') === $this->getLeft()) {
                        $this->result = $child->nodeValue == $this->getRight();
                    }
                }
                break;

            case self::TYPE_FILE_EXISTS;
                $this->result = (bool) $response->firstChild->nodeValue;
                break;
        }
    }
}
