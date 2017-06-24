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

namespace JDWil\Unify\Assertion\Shell\Core;

use JDWil\Unify\Assertion\Shell\AbstractShellAssertion;

/**
 * Class AssertCommandOutputEquals
 */
class AssertCommandOutputEquals extends AbstractShellAssertion
{
    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * AssertCommandOutputEquals constructor.
     * @param string $expectedOutput
     * @param string $file
     * @param int $line
     */
    public function __construct($expectedOutput, $file, $line)
    {
        parent::__construct($file, $line);

        $this->expectedOutput = $expectedOutput;
    }

    /**
     * @return bool
     */
    public function isPass()
    {
        return $this->result;
    }

    /**
     * @param mixed $response
     * @param int $responseNumber
     */
    public function assert($response, $responseNumber = 1)
    {
        $this->result = trim($response) === trim($this->expectedOutput);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert command output equals %s',$this->expectedOutput);
    }
}
