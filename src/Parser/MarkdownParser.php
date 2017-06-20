<?php

namespace JDWil\Unify\Parser;

use JDWil\Unify\TestRunner\TestPlan;

class MarkdownParser
{
    private $file;

    private $parserFactory;

    private $testPlans;

    private $autoloadPath;

    public function __construct($file, ParserFactory $parserFactory, $autoloadPath)
    {
        $this->file = $file;
        $this->parserFactory = $parserFactory;
        $this->autoloadPath = $autoloadPath;
        $this->testPlans = [];
    }

    public function parse()
    {
        if (preg_match_all('/```php(.*)```/Uims', file_get_contents($this->file), $m, PREG_SET_ORDER)) {
            foreach ($m as $codeBlocks) {
                $codeBlock = $codeBlocks[1];
                $codeBlock = preg_replace('/<\?php/', sprintf('<?php require_once "%s";', $this->autoloadPath), $codeBlock, 1);
                $codeBlock = $this->fixCodeBlock($codeBlock);
                $parser = $this->parserFactory->createPhpParser($this->file);
                $parser->parse($codeBlock);
                $this->testPlans[] = new TestPlan(
                    $this->file,
                    $parser->getAssertions(),
                    $codeBlock
                );
            }
        }
    }

    public function getTestPlans()
    {
        return $this->testPlans;
    }

    private function fixCodeBlock($code)
    {
        $code = sprintf("%s\nexit(0);", $code);

        return $code;
    }
}