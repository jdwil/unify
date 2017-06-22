<?php

namespace JDWil\Unify\Assertion;

/**
 * Interface AssertionInterface
 */
interface AssertionInterface
{
    /**
     * The returned commands must contain "-i %d"
     *
     * @return []
     */
    public function getDebuggerCommands();

    /**
     * @param \DOMElement $response
     * @param int $responseNumber
     */
    public function assert(\DOMElement $response, $responseNumber = 1);

    /**
     * @return int
     */
    public function getLine();

    /**
     * @return int|null
     */
    public function getIteration();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return bool
     */
    public function isPass();

    /**
     * @return string
     */
    public function getCodeContext();

    /**
     * @param $code
     */
    public function setCodeContext($code);

    /**
     * @param Context $context
     */
    public function setContext(Context $context);

    /**
     * @return string
     */
    public function __toString();
}
