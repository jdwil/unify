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

declare(strict_types=1);

namespace JDWil\Unify\Assertion\PHP\Core;

use JDWil\Unify\Assertion\PHP\AbstractPHPAssertion;
use JDWil\Unify\TestRunner\Command\CommandInterface;
use JDWil\Unify\TestRunner\Command\Debugger\Subject;
use JDWil\Unify\TestRunner\Command\ResponseInterface;

/**
 * Class AssertNotEmpty
 */
class AssertNotEmpty extends AbstractPHPAssertion
{
    /**
     * @var string
     */
    private $subject;

    /**
     * AssertEmpty constructor.
     * @param string $subject
     * @param int $iteration
     */
    public function __construct($subject, $iteration = 0)
    {
        parent::__construct($iteration);

        $this->subject = $subject;
    }

    /**
     * @param ResponseInterface $response
     * @param int $responseNumber
     * @return bool
     */
    public function assert(ResponseInterface $response, $responseNumber = 1)
    {
        parent::assert($response, $responseNumber);
        $this->result = ! $this->result;

        return $this->result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s is not empty', $this->subject);
    }

    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands()
    {
        return [
            Subject::named($this->subject)->isEmpty()
        ];
    }
}
