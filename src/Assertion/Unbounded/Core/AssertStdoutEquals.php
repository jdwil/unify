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

namespace JDWil\Unify\Assertion\Unbounded\Core;

use JDWil\Unify\Assertion\Shell\AbstractShellAssertion;
use JDWil\Unify\TestRunner\Command\ResponseInterface;

/**
 * Class AssertStdoutEquals
 */
class AssertStdoutEquals extends AbstractShellAssertion
{
    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * @var string
     */
    private $actualOutput;

    /**
     * AssertStdoutEquals constructor.
     * @param string $expectedOutput
     * @param int $file
     * @param int $line
     */
    public function __construct($expectedOutput, $file, $line)
    {
        $this->expectedOutput = $expectedOutput;

        parent::__construct($file, $line);
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
    public function getFailureMessage()
    {
        return sprintf("Expected: %s \nActual: %s\n", $this->expectedOutput, $this->actualOutput);
    }

    /**
     * @param ResponseInterface $response
     * @param int $responseNumber
     */
    public function assert(ResponseInterface $response, $responseNumber = 1)
    {
        $this->actualOutput = $response->getResponse();

        $this->result = $this->expectedOutput === $this->actualOutput;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert STDOUT equals %s', $this->expectedOutput);
    }
}
