<?php

namespace JDWil\Unify\Assertion\Core\AssertFileExists;

use JDWil\Unify\Assertion\AbstractAssertion;

/**
 * Class AssertFileExists
 */
class AssertFileExists extends AbstractAssertion
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * AssertFileExists constructor.
     * @param string $filePath
     * @param int $line
     * @param string $file
     */
    public function __construct($filePath, $line, $file)
    {
        $this->filePath = $filePath;

        parent::__construct($line, $file);
    }

    /**
     * The returned command must contain "-i %d"
     *
     * @return string
     */
    public function getDebuggerCommand()
    {
        return sprintf(
            "eval -i %%d -- %s\0",
            base64_encode(
                sprintf('file_exists(\'%s\');', $this->filePath)
            )
        );
    }

    /**
     * @param \DOMElement $response
     */
    public function assert(\DOMElement $response)
    {
        $this->result = (bool) $response->firstChild->nodeValue;
    }
}
