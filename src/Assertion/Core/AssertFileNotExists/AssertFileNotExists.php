<?php

namespace JDWil\Unify\Assertion\Core\AssertFileNotExists;

use JDWil\Unify\Assertion\AbstractAssertion;

/**
 * Class AssertFileNotExists
 */
class AssertFileNotExists extends AbstractAssertion
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * AssertFileNotExists constructor.
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
     * @return array
     */
    public function getDebuggerCommands()
    {
        return [
            sprintf(
                "eval -i %%d -- %s\0",
                base64_encode(
                    sprintf('!file_exists(\'%s\');', $this->filePath)
                )
            )
        ];
    }

    /**
     * @param \DOMElement $response
     * @param int $responseNumber
     */
    public function assert(\DOMElement $response, $responseNumber = 1)
    {
        $this->result = (bool) $response->firstChild->nodeValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert "%s" does not exist.', $this->filePath);
    }
}
