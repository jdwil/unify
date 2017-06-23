<?php
declare(strict_types=1);

namespace JDWil\Unify\Parser;

use Phlexy\Lexer\Stateful;

class UnifyParser
{
    /**
     * @var Stateful
     */
    private $lexer;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var int
     */
    private $index;

    /**
     * UnifyParser constructor.
     * @param Stateful $lexer
     */
    public function __construct(Stateful $lexer)
    {
        $this->lexer = $lexer;
    }

    public function parse($comment)
    {
        try {
            $this->tokens = $this->lexer->lex($comment);
            $this->index = -1;
        } catch (\Exception $e) {
            /* This comment isn't valid Unify code */
            return false;
        }

        $tokenGroups = $tokenGroup = [];
        while ($token = $this->skipWhitespace()) {
            if ($token[0] === UT_END_ASSERTION) {
                $tokenGroups[] = $tokenGroup;
                $tokenGroup = [];
            } else {
                $tokenGroup[] = $token;
            }
        }

        $tokenGroups[] = $tokenGroup;

        return $tokenGroups;
    }

    private function next()
    {
        if (!isset($this->tokens[$this->index + 1])) {
            return false;
        }

        return $this->tokens[++$this->index];
    }

    private function skipWhitespace()
    {
        if (!$this->next()) {
            return false;
        }

        while (isset($this->tokens[$this->index]) && $this->isWhitespace($this->tokens[$this->index])) {
            $this->index++;
        }

        return isset($this->tokens[$this->index]) ? $this->tokens[$this->index] : false;
    }

    private function isWhitespace($token)
    {
        return $token[0] === UT_WHITESPACE;
    }
}
