<?php
declare(strict_types=1);

namespace JDWil\Unify\Assertion;

/**
 * Class AssertEqual
 */
class AssertEqual implements AssertionInterface
{
    /**
     * @var string
     */
    private $variable;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $file;

    /**
     * @var bool
     */
    private $result;

    /**
     * AssertEqual constructor.
     * @param string $variable
     * @param $value
     * @param int $line
     * @param string $file
     */
    public function __construct(string $variable, $value, int $line, string $file)
    {
        $this->variable = $variable;
        $this->value = $value;
        $this->line = $line;
        $this->file = $file;
    }

    /**
     * The returned command must contain "-i %d"
     *
     * @return string
     */
    public function getDebuggerCommand()
    {
        return "context_get -i %d -d 0 -c 0\0";
    }

    /**
     * @param \DOMElement $response
     */
    public function assert(\DOMElement $response)
    {
        /** @var \DOMElement $child */
        foreach ($response->childNodes as $child) {
            if ($child->getAttribute('name') === $this->variable) {
                $this->result = $child->nodeValue == $this->value;
            }
        }
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return bool
     */
    public function isPass()
    {
        return $this->result;
    }
}
