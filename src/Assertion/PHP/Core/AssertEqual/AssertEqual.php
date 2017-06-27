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

namespace JDWil\Unify\Assertion\PHP\Core\AssertEqual;

use JDWil\Unify\Assertion\PHP\AbstractPHPAssertion;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\DbgResponse;
use JDWil\Unify\TestRunner\Command\GetValue;
use JDWil\Unify\TestRunner\Command\ResponseInterface;
use JDWil\Unify\TestRunner\Command\XdebugResponse;

/**
 * Class AssertEqual
 */
class AssertEqual extends AbstractPHPAssertion
{
    /**
     * @var string
     */
    private $variable;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var mixed
     */
    private $internalValue;

    /**
     * AssertEqual constructor.
     * @param string $variable
     * @param $value
     * @param int $line
     * @param int $iteration
     * @param string $file
     */
    public function __construct($variable, $value, $line, $iteration, $file)
    {
        $this->variable = $variable;
        $this->value = $value;

        parent::__construct($line, $file, $iteration);
    }

    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands()
    {
        return [
            GetValue::of($this->variable)
        ];
    }

    /**
     * @param ResponseInterface $response
     * @param int $responseNumber
     */
    public function assert(ResponseInterface $response, $responseNumber = 1)
    {
        if ($response instanceof XdebugResponse) {
            $this->result = $response->getValueOf($this->variable) === $this->value;
        } else if ($response instanceof DbgResponse) {
            $this->result = $response->getResponse() === $this->value;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s equals %s.', $this->variable, (string) $this->value);
    }

    /**
     * @return bool
     */
    private function valueNeedsEvaluation()
    {
        return strpos($this->value, '::') !== false;
    }
}
