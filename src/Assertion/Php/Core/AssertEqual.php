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

use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\Debugger\Dump;
use JDWil\Unify\TestRunner\Command\Debugger\Subject;
use JDWil\Unify\TestRunner\Command\ResponseInterface;
use JDWil\Unify\TestRunner\Command\XDebugResponse;

/**
 * Class AssertEqual
 */
class AssertEqual extends AbstractComparisonAssertion
{
    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands()
    {
        $equals = Subject::named($this->variable)->equals($this->value);
        if ($this->block) {
            $equals->trimmed();
        }

        return [$equals];
    }

    /**
     * @return CommandInterface[]
     */
    public function getFailureCommands()
    {
        return [
            Dump::variable($this->variable)
        ];
    }

    /**
     * @param ResponseInterface $response
     */
    public function handleFailureCommandResponse(ResponseInterface $response)
    {
        if ($response instanceof XDebugResponse) {
            $this->actualValue = base64_decode($response->getEvalResponse());
        } else {
            $this->actualValue = $response->getResponse();
        }
    }

    /**
     * @return string
     */
    public function getFailureMessage()
    {
        return sprintf("Expected: '%s'\nActual: '%s'\n", $this->value, $this->actualValue);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s equals %s.', $this->variable, (string) $this->value);
    }
}
