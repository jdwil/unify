<?php
declare(strict_types=1);

namespace JDWil\Unify\Assertion;

/**
 * Class AssertEqual
 */
class AssertEqual extends AbstractAssertion
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

        parent::__construct($line, $file);
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
}
