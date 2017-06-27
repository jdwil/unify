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

namespace JDWil\Unify\TestRunner\PHP;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractSession
 */
abstract class AbstractSession implements SessionInterface
{
    const INITIALIZE = 0;
    const RUNNING = 1;
    const ASSERTING = 2;
    const POSTMORTEM = 3;
    const STOPPED = 4;
    const CONTINUING = 5;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var int[]
     */
    protected $context;

    /**
     * AbstractSession constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->loop = Factory::create();
        $this->context = [];
    }

    /**
     * @return int
     */
    protected function context()
    {
        return end($this->context);
    }

    /**
     * @param int $context
     */
    protected function pushContext($context)
    {
        $this->context[] = $context;
    }

    /**
     * @param int $context
     */
    protected function setContext($context)
    {
        $this->context = [$context];
    }

    protected function popContext()
    {
        array_pop($this->context);
    }
}
