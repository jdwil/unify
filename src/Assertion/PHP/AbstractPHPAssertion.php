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

use JDWil\Unify\TestRunner\Command\DbgResponse;
use JDWil\Unify\TestRunner\Command\ResponseInterface;
use JDWil\Unify\TestRunner\Command\XdebugResponse;
use JDWil\Unify\ValueObject\PHPContext;
use JDWil\Unify\ValueObject\LineRange;

/**
 * Class AbstractPHPAssertion
 */
abstract class AbstractPHPAssertion implements PHPAssertionInterface
{
    /**
     * @var int
     */
    protected $iteration;

    /**
     * @var bool
     */
    protected $result;

    /**
     * @var PHPContext
     */
    protected $context;

    /**
     * AbstractAssertion constructor.
     * @param int $iteration
     */
    public function __construct($iteration)
    {
        $this->iteration = $iteration;
    }

    /**
     * @return LineRange
     */
    public function getLine()
    {
        return $this->context->getLine();
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->context->getFile();
    }

    /**
     * @return bool
     */
    public function isPass()
    {
        return $this->result;
    }

    /**
     * @param PHPContext $context
     */
    public function setContext(PHPContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return int
     */
    public function getIteration()
    {
        return $this->iteration;
    }

    /**
     * @param int $iteration
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
     * @param ResponseInterface $response
     * @param int $responseNumber
     */
    public function assert(ResponseInterface $response, $responseNumber = 1)
    {
        if ($response instanceof XdebugResponse) {
            $this->result = (bool) $response->getEvalResponse();
        } else if ($response instanceof DbgResponse) {
            $this->result = (bool) $response->getResponse();
        }
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
