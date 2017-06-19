<?php
declare(strict_types=1);

namespace JDWil\Unify\Assertion;

/**
 * Interface AssertionInterface
 */
interface AssertionInterface
{
    /**
     * The returned command must contain "-i %d"
     *
     * @return string
     */
    public function getDebuggerCommand();

    /**
     * @param \DOMElement $response
     */
    public function assert(\DOMElement $response);

    /**
     * @return int
     */
    public function getLine();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return bool
     */
    public function isPass();
}
