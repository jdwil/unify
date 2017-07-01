<?php
/**
 * Copyright (c) 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details. You should have received a copy of the GNU Lesser General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\Assertion\PHP;
use JDWil\Unify\Parser\Unify\PHP\PHPContext;
use JDWil\Unify\ValueObject\LineRange;

/**
 * Class AbstractPHPAssertion
 */
abstract class AbstractPHPAssertion implements PHPAssertionInterface
{
    /**
     * @var LineRange
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
     * @var PHPContext
     */
    protected $context;

    /**
     * AbstractAssertion constructor.
     * @param LineRange $line
     * @param string $file
     * @param int $iteration
     */
    public function __construct(LineRange $line, $file, $iteration)
    {
        $this->line = $line;
        $this->file = $file;
        $this->codeContext = '';
        $this->iteration = $iteration;
    }

    /**
     * @return LineRange
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
     * @param PHPContext $context
     */
    public function setContext(PHPContext $context)
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

    public function __clone()
    {
        $this->result = null;
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

    /**
     * @param $code
     * @return bool|string
     */
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
