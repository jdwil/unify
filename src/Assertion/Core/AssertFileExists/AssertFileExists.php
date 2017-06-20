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
     * @return array
     */
    public function getDebuggerCommands()
    {
        return [
            sprintf(
                "eval -i %%d -- %s\0",
                base64_encode(
                    sprintf('file_exists(\'%s\');', $this->filePath)
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
        return sprintf('Assert "%s" exists.', $this->filePath);
    }
}
