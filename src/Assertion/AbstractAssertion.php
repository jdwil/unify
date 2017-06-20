<?php

namespace JDWil\Unify\Assertion;


abstract class AbstractAssertion implements AssertionInterface
{
    /**
     * @var int
     */
    protected $line;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var bool
     */
    protected $result;

    /**
     * @var string
     */
    protected $codeContext;

    /**
     * @var Context
     */
    protected $context;

    /**
     * AbstractAssertion constructor.
     * @param int $line
     * @param string $file
     */
    public function __construct($line, $file)
    {
        $this->line = $line;
        $this->file = $file;
        $this->codeContext = '';
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

    /**
     * @return string
     */
    public function getCodeContext()
    {
        return $this->codeContext;
    }

    /**
     * @param $code
     */
    public function setCodeContext($code)
    {
        $this->codeContext = $code;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}
