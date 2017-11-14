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
use JDWil\Unify\TestRunner\Php\PhpTestPlan;
use JDWil\Unify\TestRunner\Shell\ShellTestPlan;
use JDWil\Unify\TestRunner\TestPlanInterface;
use JDWil\Unify\TestRunner\Unbounded\UnboundedTestPlan;
use Phlexy\Lexer\Stateful;

/**
 * Class MarkdownParser
 */
class MarkdownParser
{
    const SKIP_NEXT = 'skip';
    const SETUP = 'setup';

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
     * @var string
     */
    private $setupCode;

    /**
     * @var array
     */
    private $directives;

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
        $this->directives = [];
        $this->setupCode = '';
    }

    /**
     * @param string $file
     * @throws \Exception
     */
    public function parse($file)
    {
        $this->file = $file;

        $tokens = $this->lexer->lex(file_get_contents($file));

        foreach ($tokens as $token) {
            switch ($token[0]) {
                case MD_DIRECTIVE:
                    $this->addDirective($token[2], true);
                    break;

                case MD_PHP_CODE:
                    if (!$this->hasDirective(self::SKIP_NEXT)) {
                        $this->createPhpTestPlan($token[2]);
                    }

                    if ($this->hasDirective(self::SETUP)) {
                        $this->appendSetupCode($token[2]);
                    }

                    $this->clearDirectives();
                    break;

                case MD_SHELL_CODE:
                    if (!$this->hasDirective(self::SKIP_NEXT)) {
                        $this->createShellTestPlan($token[2]);
                    }

                    $this->clearDirectives();
                    break;

                case MD_STDOUT:
                    if (!$this->hasDirective(self::SKIP_NEXT)) {
                        $this->createUnboundedTestPlan($token[2]);
                    }

                    $this->clearDirectives();
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
        if (!empty($this->setupCode)) {
            $codeBlock = preg_replace('/<\?php/', sprintf('<?php %s', $this->setupCode), $codeBlock, 1);
        }

        $codeBlock = preg_replace('/<\?php/', sprintf('<?php require_once "%s";', $this->autoloadPath), $codeBlock, 1);
        $codeBlock = $this->fixCodeBlock($codeBlock);
        $parser = $this->parserFactory->createPhpParser($this->file);
        $parser->parse($codeBlock);
        $this->testPlans[] = new PhpTestPlan(
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

    /**
     * @param string $directive
     * @return bool
     */
    private function hasDirective($directive)
    {
        return isset($this->directives[$directive]) && $this->directives[$directive];
    }

    /**
     * @param string $directive
     * @param mixed $value
     */
    private function addDirective($directive, $value)
    {
        $this->directives[$directive] = $value;
    }

    private function clearDirectives()
    {
        $this->directives = [];
    }

    /**
     * @param string $code
     */
    private function appendSetupCode($code)
    {
        $this->setupCode .= str_replace('<?php', '', $code);
    }
}
