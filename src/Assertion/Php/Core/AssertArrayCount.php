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

namespace JDWil\Unify\Assertion\Php\Core;

use JDWil\Unify\Assertion\Php\AbstractPhpAssertion;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\Debugger\Subject;

/**
 * Class AssertArrayCount
 */
class AssertArrayCount extends AbstractPhpAssertion
{
    /**
     * @var string
     */
    private $variable;

    /**
     * @var int
     */
    private $count;

    /**
     * AssertArrayCount constructor.
     * @param string $variable
     * @param int $count
     * @param int $iteration
     */
    public function __construct($variable, $count, $iteration = 0)
    {
        parent::__construct($iteration);

        $this->variable = $variable;
        $this->count = $count;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s contains %d elements.', $this->variable, $this->count);
    }

    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands()
    {
        return [
            Subject::named(sprintf('count(%s)', $this->variable))->equals($this->count)
        ];
    }
}
