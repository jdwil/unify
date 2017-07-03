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

namespace JDWil\Unify\TestRunner\Unbounded;

use JDWil\Unify\TestRunner\Command\CommandResponse;
use JDWil\Unify\TestRunner\PHP\PHPTestPlan;
use JDWil\Unify\TestRunner\TestPlanInterface;

/**
 * Class UnboundedTester
 */
class UnboundedTester
{
    /**
     * @var TestPlanInterface[]
     */
    private $testPlans;

    /**
     * @var int
     */
    private $index;

    /**
     * UnboundedTester constructor.
     * @param TestPlanInterface[] $testPlans
     * @param int $index
     */
    public function __construct($testPlans, $index)
    {
        $this->testPlans = $testPlans;
        $this->index = $index;
    }

    /**
     * @param UnboundedTestPlan $testPlan
     */
    public function test(UnboundedTestPlan $testPlan)
    {
        $lastTestPlan = $this->testPlans[$this->index - 1];
        if ($lastTestPlan instanceof PHPTestPlan) {
            $stdout = $lastTestPlan->getOutput();
        } else {
            return;
        }

        foreach ($testPlan->getAssertions() as $assertion) {
            $assertion->assert(
                new CommandResponse($stdout)
            );
        }
    }
}
