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
     * @param int $iteration
     * @param string $file
     */
    public function __construct($filePath, $line, $iteration, $file)
    {
        $this->filePath = $filePath;

        parent::__construct($line, $file, $iteration);
    }

    /**
     * The returned command must contain "-i %d"
     *
     * @return array
     */
    public function getDebuggerCommands()
    {
        $ret = [];
        $ret[] = sprintf(
            "eval -i %%d -- %s\0",
            base64_encode(
                sprintf('file_exists(\'%s\');', $this->filePath)
            )
        );

        return $ret;
    }

    /**
     * @param \DOMElement $response
     * @param int $responseNumber
     */
    public function assert(\DOMElement $response, $responseNumber = 1)
    {
        if (!$this->result) {
            $this->result = (bool) $response->firstChild->nodeValue;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert "%s" exists.', $this->filePath);
    }
}
