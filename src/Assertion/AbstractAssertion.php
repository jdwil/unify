<?php

namespace JDWil\Unify\Assertion;

/**
 * Class AbstractAssertion
 */
abstract class AbstractAssertion implements AssertionInterface
{
    /**
     * @var int
     */
    protected $line;

    /**
     * @var int|null
     */
    protected $iteration;

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
     * @param int $iteration
     */
    public function __construct($line, $file, $iteration = null)
    {
        $this->line = $line;
        $this->file = $file;
        $this->codeContext = '';
        $this->iteration = $iteration;
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

    /**
     * @return int|null
     */
    public function getIteration()
    {
        return $this->iteration;
    }

    /**
     * @param int|null $iteration
     */
    public function setIteration($iteration)
    {
        $this->iteration = $iteration;
    }

    /**
     * @param string $code
     * @return string
     */
    protected function prepareEvalCode($code)
    {
        return sprintf(
            'require_once "%s"; %s %s;',
            $this->context->getAutoloadPath(),
            implode(' ', $this->context->getUseStatements()),
            $code
        );
    }

    protected function fullyQualifyClassConstant($code)
    {
        if (strpos($code, '::') !== false) {
            list($class, $constant) = explode('::', $code);
            foreach ($this->context->getUseStatements() as $useStatement) {
                if (preg_match("~{$class};~", $useStatement)) {
                    preg_match('~use ([^;]+);~', $useStatement, $m);
                    return sprintf('%s::%s', $m[1], $constant);
                }
            }
        }

        return false;
    }
}
