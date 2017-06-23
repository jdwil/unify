<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

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
