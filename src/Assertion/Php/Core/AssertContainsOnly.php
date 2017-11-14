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

class AssertContainsOnly extends AbstractPhpAssertion
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $type;

    /**
     * AssertContainsOnly constructor.
     * @param string $subject
     * @param string $type
     * @param int $iteration
     */
    public function __construct($subject, $type, $iteration = 0)
    {
        parent::__construct($iteration);

        $this->subject = $subject;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s contains only %s', $this->subject, (string) $this->type);
    }

    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands()
    {
        return [
            Subject::named($this->subject)->containsOnly($this->type)
        ];
    }
}
