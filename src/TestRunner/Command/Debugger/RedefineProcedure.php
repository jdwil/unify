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

namespace JDWil\Unify\TestRunner\Command\Debugger;

use JDWil\Unify\TestRunner\Command\AbstractCommand;

/**
 * Class RedefineFunction
 */
class RedefineProcedure extends AbstractCommand
{
    /**
     * @var string
     */
    private $functionName;

    /**
     * @var string
     */
    private $body;

    private function __construct() {}

    /**
     * $body must be a string that contains the code for a closure.
     *
     * @param string $functionName
     * @return RedefineProcedure
     */
    public static function named($functionName)
    {
        $ret = new RedefineProcedure();
        $ret->functionName = $functionName;

        return $ret;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function toExecute($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getXdebugCommand()
    {
        if ($this->isMethod()) {
            list($class, $method) = explode('::', $this->functionName);

            return sprintf(
                "eval -i %%d -- %s\0",
                base64_encode(sprintf('runkit_method_redefine("%s", "%s", %s);', $class, $method, $this->body))
            );
        }

        return sprintf(
            "eval -i %%d -- %s\0",
            base64_encode(sprintf('runkit_function_redefine("%s", %s);', $this->functionName, $this->body))
        );
    }

    /**
     * @return string
     */
    public function getDbgCommand()
    {
        if ($this->isMethod()) {
            list($class, $method) = explode('::', $this->functionName);

            return sprintf("ev runkit_method_redefine('%s', '%s', %s)", $class, $method, $this->body);
        }

        return sprintf("ev runkit_function_redefine('%s', %s)", $this->functionName, $this->body);
    }

    /**
     * @return bool
     */
    private function isMethod()
    {
        return strpos($this->functionName, '::') !== false;
    }
}
