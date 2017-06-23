<?php
declare(strict_types=1);

namespace JDWil\Unify\Assertion;

/**
 * Class AbstractAssertionParser
 */
abstract class AbstractAssertionParser implements AssertionParserInterface
{
    const TYPE = 0;
    const LINE = 1;
    const VALUE = 2;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param $comment
     * @param Context $context
     */
    public function initialize($comment, Context $context)
    {
        $this->tokens = $comment;
        $this->context = $context;
        $this->index = -1;
    }

    /**
     * @return bool
     */
    protected function next()
    {
        if (!isset($this->tokens[$this->index + 1])) {
            return false;
        }

        return $this->tokens[++$this->index];
    }

    /**
     * @param int $lookahead
     * @return bool
     */
    protected function peek($lookahead = 1)
    {
        if (!isset($this->tokens[$this->index + $lookahead])) {
            return false;
        }

        return $this->tokens[$this->index + $lookahead];
    }

    protected function reset()
    {
        $this->index = -1;
    }

    /**
     * @param $tokenTypes
     * @return bool
     */
    protected function containsToken($tokenTypes)
    {
        $start = $this->index;
        $this->reset();

        while ($token = $this->next()) {
            if (in_array($token[0], $tokenTypes, true)) {
                $this->index = $start;
                return true;
            }
        }

        $this->index = $start;

        return false;
    }
}
