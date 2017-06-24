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

namespace JDWil\Unify\Assertion\PHP;

class PHPContext
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $assignmentVariable;

    /**
     * @var string
     */
    private $codeContext;

    /**
     * @var array
     */
    private $useStatements;

    /**
     * @var string
     */
    private $autoloadPath;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->codeContext = '';
        $this->useStatements = [];
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getAssignmentVariable()
    {
        return $this->assignmentVariable;
    }

    /**
     * @param string $assignmentVariable
     */
    public function setAssignmentVariable($assignmentVariable)
    {
        $this->assignmentVariable = $assignmentVariable;
    }

    /**
     * @return string
     */
    public function getCodeContext()
    {
        return $this->codeContext;
    }

    /**
     * @param string $codeContext
     */
    public function setCodeContext($codeContext)
    {
        $this->codeContext = $codeContext;
    }

    /**
     * @param $code
     */
    public function appendCodeContext($code)
    {
        $this->codeContext .= $code;
    }

    public function resetCodeContext()
    {
        $this->codeContext = '';
    }

    public function addUseStatement($statement)
    {
        $this->useStatements[] = $statement;
    }

    public function getUseStatements()
    {
        return $this->useStatements;
    }

    public function setAutoloadPath($autoloadPath)
    {
        $this->autoloadPath = $autoloadPath;
    }

    public function getAutoloadPath()
    {
        return $this->autoloadPath;
    }
}
