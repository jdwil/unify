<?php
declare(strict_types=1);

namespace JDWil\Unify\Debugger;

use JDWil\Unify\TestRunner\Assertion;

class DebugPlan
{
    private $commandQueue;

    private $steps;

    /**
     * DebugPlan constructor.
     * @param Assertion[] $assertions
     */
    public function __construct(array $assertions)
    {
        $this->steps = [];
        $this->commandQueue = [
            DebugStep::toRunCommand("feature_set -i %d -n show_hidden -v 1\0"),
            DebugStep::toRunCommand("feature_set -i %d -n max_children -v 100\0"),
            DebugStep::toRunCommand("feature_set -i %d -n extended_properties -v 1\0"),
            DebugStep::toRunCommand("feature_get -i %d -n supports_postmortem\0"),
        ];

        $this->buildPlan($assertions);
    }

    /**
     * @return DebugStep
     */
    public function nextStep()
    {
        $step = array_shift($this->commandQueue);
        $this->steps[] = $step;

        return $step;
    }

    public function isComplete()
    {
        return empty($this->commandQueue);
    }

    public function getQueue()
    {
        return $this->commandQueue;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @param Assertion[] $assertions
     */
    private function buildPlan(array $assertions)
    {
        foreach ($assertions as $assertion) {
            switch ($assertion->getType()) {
                case Assertion::TYPE_EQUALS:
                    $this->commandQueue[] = DebugStep::toRunCommand(
                        sprintf(
                            "breakpoint_set -i %%d -t line -f %s -n %d\0",
                            $assertion->getFile(),
                            $assertion->getLine()
                        )
                    );
                    break;
            }
        }

        $this->commandQueue[] = DebugStep::toRunCommand("run -i %d\0");

        foreach ($assertions as $assertion) {
            switch ($assertion->getType()) {
                case Assertion::TYPE_EQUALS:
                    //$this->commandQueue[] = DebugStep::toRunCommand("step_over -i %d\0");
                    $this->commandQueue[] = DebugStep::toGetValue($assertion->getLeft());
                    $this->commandQueue[] = DebugStep::toRunCommand("run -i %d\0");
                    break;
            }
        }
    }
}
