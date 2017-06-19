<?php
declare(strict_types=1);

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
     * AbstractAssertion constructor.
     * @param int $line
     * @param string $file
     */
    public function __construct(int $line, string $file)
    {
        $this->line = $line;
        $this->file = $file;
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