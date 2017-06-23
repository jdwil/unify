<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\TestRunner;

use JDWil\Unify\Assertion\AssertionInterface;
use JDWil\Unify\Assertion\AssertionQueue;

/**
 * Class TestPlan
 */
class TestPlan
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $source;

    /**
     * @var AssertionQueue
     */
    private $assertionQueue;

    /**
     * TestPlan constructor.
     * @param string $file
     * @param AssertionQueue $assertionQueue
     * @param string $source
     */
    public function __construct($file, AssertionQueue $assertionQueue, $source = null)
    {
        $this->file = $file;
        $this->source = $source;
        $this->assertionQueue = $assertionQueue;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return null|string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return AssertionQueue
     */
    public function getAssertionQueue()
    {
        return $this->assertionQueue;
    }

    /**
     * @return AssertionInterface[]
     */
    public function getAssertions()
    {
        return $this->assertionQueue->all();
    }

    /**
     * @return bool
     */
    public function isPass()
    {
        foreach ($this->assertionQueue->all() as $assertion) {
            if (!$assertion->isPass()) {
                return false;
            }
        }

       return true;
    }
}
