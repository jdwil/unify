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

namespace JDWil\Unify\Assertion\PHP\Core;

use JDWil\Unify\Assertion\PHP\AbstractPHPAssertion;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\Debugger\ArrayKey;

/**
 * Class AssertArrayHasKey
 */
class AssertArrayHasKey extends AbstractPHPAssertion
{
    /**
     * @var string
     */
    private $arrayVariable;

    /**
     * @var string
     */
    private $key;

    /**
     * AssertArrayHasKey constructor.
     * @param int $arrayVariable
     * @param string $key
     * @param int $iteration
     */
    public function __construct($arrayVariable, $key, $iteration = 0)
    {
        $this->arrayVariable = $arrayVariable;
        $this->key = $key;

        parent::__construct($iteration);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s contains %s', $this->arrayVariable, (string) $this->key);
    }

    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands()
    {
        $variable = strpos($this->arrayVariable, '[') !== false ?
            $this->arrayVariable :
            sprintf('%s[%s]', $this->arrayVariable, (string) $this->key)
        ;

        return [
            ArrayKey::exists($variable)
        ];
    }
}
