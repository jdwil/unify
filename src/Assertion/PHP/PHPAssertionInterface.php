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

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\TestRunner\Command\ResponseInterface;
use JDWil\Unify\ValueObject\PHPContext;
use JDWil\Unify\TestRunner\Command\CommandInterface;

/**
 * Interface PHPAssertionInterface
 */
interface PHPAssertionInterface extends AssertionInterface
{
    /**
     * @return CommandInterface[]
     */
    public function getDebuggerCommands();

    /**
     * @return false|CommandInterface[]
     */
    public function getFailureCommands();

    /**
     * @param ResponseInterface $response
     */
    public function handleFailureCommandResponse(ResponseInterface $response);

    /**
     * @return int|null
     */
    public function getIteration();

    /**
     * @param int $iteration
     */
    public function setIteration($iteration);

    /**
     * @param PHPContext $context
     */
    public function setContext(PHPContext $context);

    public function __clone();
}
