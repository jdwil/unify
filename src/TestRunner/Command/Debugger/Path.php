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
 * Class Path
 */
class Path extends AbstractCommand
{
    const EXISTS = 'exists';
    const NOT_EXISTS = 'not_exists';
    const IS_READABLE = 'is_readable';
    const IS_NOT_READABLE = 'is_not_readable';
    const IS_WRITABLE = 'is_writable';
    const IS_NOT_WRITABLE = 'is_not_writable';

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $validation;

    private function __construct() {}

    /**
     * @param string $path
     * @return Path
     */
    public static function at($path)
    {
        $ret = new Path();
        $ret->path = $path;

        return $ret;
    }

    /**
     * @return $this
     */
    public function exists()
    {
        $this->validation = self::EXISTS;

        return $this;
    }

    /**
     * @return $this
     */
    public function doesNotExist()
    {
        $this->validation = self::NOT_EXISTS;

        return $this;
    }

    /**
     * @return $this
     */
    public function isReadable()
    {
        $this->validation = self::IS_READABLE;

        return $this;
    }

    /**
     * @return $this
     */
    public function isNotReadable()
    {
        $this->validation = self::IS_NOT_READABLE;

        return $this;
    }

    /**
     * @return $this
     */
    public function isWritable()
    {
        $this->validation = self::IS_WRITABLE;

        return $this;
    }

    /**
     * @return $this
     */
    public function isNotWritable()
    {
        $this->validation = self::IS_NOT_WRITABLE;

        return $this;
    }

    /**
     * @return string
     */
    public function getXdebugCommand()
    {
        return sprintf(
            "eval -i %%d -- %s\0",
            base64_encode($this->getEvalStatement())
        );
    }

    /**
     * @return string
     */
    public function getDbgCommand()
    {
        return sprintf('ev %s', $this->getEvalStatement());
    }

    /**
     * @return string
     */
    private function getEvalStatement()
    {
        $path = trim($this->path, "'\"");

        switch ($this->validation) {
            case self::EXISTS:
            case self::NOT_EXISTS:
                return sprintf('file_exists("%s")', $path);
            case self::IS_READABLE:
            case self::IS_NOT_READABLE:
                return sprintf('is_readable("%s")', $path);
            case self::IS_WRITABLE:
            case self::IS_NOT_WRITABLE:
                return sprintf('is_writable("%s")', $path);
        }
    }
}
