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

namespace JDWil\Unify\Assertion;

/**
 * Class AbstractAssertionQueue
 */
class AbstractAssertionQueue implements AssertionQueueInterface
{
    /**
     * @var AssertionInterface[]
     */
    protected $assertions;

    /**
     * @var AssertionInterface[]
     */
    protected $cache;

    /**
     * AssertionQueue constructor.
     */
    public function __construct()
    {
        $this->assertions = [];
        $this->cache = [];
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->assertions);
    }

    /**
     * @return AssertionInterface[]
     */
    public function getQueue()
    {
        return $this->assertions;
    }

    /**
     * @return AssertionInterface[]
     */
    public function all()
    {
        return $this->cache;
    }

    /**
     * @param AssertionInterface $assertion
     */
    public function add(AssertionInterface $assertion)
    {
        $this->assertions[] = $assertion;
        $this->cache[] = $assertion;
    }

    /**
     * @return AssertionInterface
     */
    public function current()
    {
        return isset($this->assertions[0]) ? $this->assertions[0] : null;
    }

    /**
     * @return AssertionInterface
     */
    public function next()
    {
        return array_shift($this->assertions);
    }
}
