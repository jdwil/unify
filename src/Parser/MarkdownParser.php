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

use JDWil\Unify\Assertion\Unbounded\Core\AssertStdoutEquals;
use JDWil\Unify\Assertion\Unbounded\UnboundedAssertionQueue;
use JDWil\Unify\TestRunner\PHP\PHPTestPlan;
use JDWil\Unify\TestRunner\Shell\ShellTestPlan;
use JDWil\Unify\TestRunner\TestPlanInterface;
use JDWil\Unify\TestRunner\Unbounded\UnboundedTestPlan;
use Phlexy\Lexer\Stateful;

/**
 * Class MarkdownParser
 */
class MarkdownParser
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var Stateful
     */
    private $lexer;

    /**
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * @var TestPlanInterface[]
     */
    private $testPlans;

    /**
     * @var string
     */
    private $autoloadPath;

    /**
     * MarkdownParser constructor.
     * @param Stateful $lexer
     * @param ParserFactory $parserFactory
     * @param string $autoloadPath
     */
    public function __construct(Stateful $lexer, ParserFactory $parserFactory, $autoloadPath)
    {
        $this->lexer = $lexer;
        $this->parserFactory = $parserFactory;
        $this->autoloadPath = $autoloadPath;
        $this->testPlans = [];
    }

    /**
     * @param string $file
     */
    public function parse($file)
    {
        $this->file = $file;

        $tokens = $this->lexer->lex(file_get_contents($file));

        $skipNextBlock = false;
        foreach ($tokens as $token) {
            switch ($token[0]) {
                case MD_DIRECTIVE:
                    if ($token[2] === 'skip') {
                        $skipNextBlock = true;
                    }
                    break;

                case MD_PHP_CODE:
                    if ($skipNextBlock) {
                        $skipNextBlock = false;
                    } else {
                        $this->createPhpTestPlan($token[2]);
                    }
                    break;

                case MD_SHELL_CODE:
                    if ($skipNextBlock) {
                        $skipNextBlock = false;
                    } else {
                        $this->createShellTestPlan($token[2]);
                    }
                    break;

                case MD_STDOUT:
                    if ($skipNextBlock) {
                        $skipNextBlock = false;
                    } else {
                        $this->createUnboundedTestPlan($token[2]);
                    }
                    break;
            }
        }

    }

    /**
     * @return TestPlanInterface[]
     */
    public function getTestPlans()
    {
        return $this->testPlans;
    }

    /**
     * @param string $codeBlock
     * @throws \Exception
     */
    private function createPhpTestPlan($codeBlock)
    {
        $codeBlock = preg_replace('/<\?php/', sprintf('<?php require_once "%s";', $this->autoloadPath), $codeBlock, 1);
        $codeBlock = $this->fixCodeBlock($codeBlock);
        $parser = $this->parserFactory->createPhpParser($this->file);
        $parser->parse($codeBlock);
        $this->testPlans[] = new PHPTestPlan(
            $this->file,
            $codeBlock,
            $parser->getAssertions(),
            $parser->getCommands()
        );
    }

    /**
     * @param string $codeBlock
     */
    private function createShellTestPlan($codeBlock)
    {
        $parser = $this->parserFactory->createShellParser($this->file);
        $parser->parse($codeBlock);
        $this->testPlans[] = new ShellTestPlan(
            $this->file,
            $parser->getCommand(),
            $parser->getAssertions()
        );
    }

    /**
     * @param string $codeBlock
     */
    private function createUnboundedTestPlan($codeBlock)
    {
        $queue = new UnboundedAssertionQueue();
        $queue->add(new AssertStdoutEquals($codeBlock, $this->file, 0));

        $this->testPlans[] = new UnboundedTestPlan($this->file, $codeBlock, $queue);
    }

    /**
     * @param string $code
     * @return string
     */
    private function fixCodeBlock($code)
    {
        $code = sprintf("%s\nexit(0);", $code);

        return $code;
    }
}
