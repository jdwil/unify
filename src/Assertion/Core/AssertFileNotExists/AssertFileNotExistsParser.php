<?php

namespace JDWil\Unify\Assertion\Core\AssertFileNotExists;

use JDWil\Unify\Assertion\AbstractAssertionParser;
use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\Context;

/**
 * Class AssertFileNotExistsMatcher
 */
class AssertFileNotExistsParser extends AbstractAssertionParser
{
    /**
     * @var array
     */
    private $files;

    /**
     * @param $comment
     * @param Context $context
     */
    public function initialize($comment, Context $context)
    {
        parent::initialize($comment, $context);

        $this->files = [];
    }

    /**
     * @return AssertionInterface[]|false
     */
    public function parse()
    {
        if (!$this->containsToken([UT_FILE_NOT_EXISTS])) {
            return false;
        }

        while ($token = $this->next()) {
            switch ($token[self::TYPE]) {
                case UT_FILE_PATH:
                    $this->files[] = $token[self::VALUE];
                    break;
            }
        }

        $assertions = [];
        foreach ($this->files as $index => $file) {
            $assertions[] = new AssertFileNotExists(
                $file,
                $this->context->getLine(),
                count($this->files) > 1 ? $index + 1 : 0,
                $this->context->getFile()
            );
        }

        return $assertions;
    }
}
