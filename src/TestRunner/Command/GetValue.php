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

namespace JDWil\Unify\TestRunner\Command;

/**
 * Class GetValue
 */
class GetValue implements CommandInterface
{
    /**
     * @var string
     */
    private $variable;

    /**
     * @var ResponseInterface
     */
    private $response;

    private function __construct() {}

    /**
     * @param $variable
     * @return GetValue
     */
    public static function of($variable)
    {
        $ret = new GetValue();
        $ret->variable = $variable;

        return $ret;
    }

    /**
     * @return string
     */
    public function getXdebugCommand()
    {
        return "context_get -i %d -d 0 -c 0\0";
    }

    /**
     * @return string
     */
    public function getDbgCommand()
    {
        return sprintf('ev %s', $this->variable);
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
