<?php

namespace JDWil\Unify\Assertion;

/**
 * Class Pipeline
 */
class Pipeline
{
    /**
     * @var AssertionParserInterface[]
     */
    private $matchers;

    /**
     * Pipeline constructor.
     */
    public function __construct()
    {
        $this->matchers = [];
    }

    /**
     * @param AssertionParserInterface $matcher
     */
    public function addMatcher(AssertionParserInterface $matcher)
    {
        $this->matchers[] = $matcher;
    }

    /**
     * @param array $comment
     * @param Context $context
     * @return bool|false|AssertionInterface[]
     */
    public function handleComment($comment, Context $context)
    {
        foreach ($this->matchers as $matcher) {
            $matcher->initialize($comment, $context);
            if ($assertion = $matcher->parse()) {
                return $assertion;
            }
        }

        return false;
    }
}
