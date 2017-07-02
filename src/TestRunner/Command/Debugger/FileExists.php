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
 * Class FileExists
 */
class FileExists extends AbstractCommand
{
    /**
     * @var string
     */
    private $path;

    private function __construct() {}

    /**
     * @param string $path
     * @return FileExists
     */
    public static function atPath($path)
    {
        $ret = new FileExists();
        $ret->path = $path;

        return $ret;
    }

    /**
     * @return string
     */
    public function getXdebugCommand()
    {
        return sprintf(
            "eval -i %%d -- %s\0",
            base64_encode(
                sprintf('file_exists(%s);', $this->path)
            )
        );
    }

    /**
     * @return string
     */
    public function getDbgCommand()
    {
        return sprintf('ev file_exists("%s")', $this->path);
    }
}
