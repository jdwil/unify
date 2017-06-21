<?php

namespace JDWil\Unify\Assertion\Core\AssertFileNotExists;

use JDWil\Unify\Assertion\AbstractAssertion;

/**
 * Class AssertFileNotExists
 */
class AssertFileNotExists extends AbstractAssertion
{
    /**
     * @var array
     */
    private $filePaths;

    /**
     * AssertFileNotExists constructor.
     * @param array $filePaths
     * @param int $line
     * @param string $file
     */
    public function __construct($filePaths, $line, $file)
    {
        $this->filePaths = $filePaths;
        parent::__construct($line, $file);
        $this->result = true;
    }

    /**
     * The returned command must contain "-i %d"
     *
     * @return array
     */
    public function getDebuggerCommands()
    {
        $ret = [];

        foreach ($this->filePaths as $filePath) {
            $ret[] = sprintf(
                "eval -i %%d -- %s\0",
                base64_encode(
                    sprintf('!file_exists(\'%s\');', $filePath)
                )
            );
        }

        return $ret;
    }

    /**
     * @param \DOMElement $response
     * @param int $responseNumber
     */
    public function assert(\DOMElement $response, $responseNumber = 1)
    {
        if ($this->result) {
            $this->result = (bool) $response->firstChild->nodeValue;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert "%s" does not exist.', implode(', ', $this->filePaths));
    }
}
