<?php
declare(strict_types=1);

namespace JDWil\Unify\Assertion;

/**
 * Class AssertFileExists
 */
class AssertFileExists implements AssertionInterface
{
    /**
     * @var string
     */
    private $filePath;

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
     * AssertFileExists constructor.
     * @param string $filePath
     * @param int $line
     * @param string $file
     */
    public function __construct(string $filePath, int $line, string $file)
    {
        $this->filePath = $filePath;
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
