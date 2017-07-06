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

namespace JDWil\Unify\ValueObject;

/**
 * Class CodeCoverage
 */
class CodeCoverage
{
    /**
     * @var array
     */
    private $coverage;

    /**
     * CodeCoverage constructor.
     */
    public function __construct()
    {
        $this->coverage = [];
    }

    /**
     * @param array $coverage
     */
    public function addCoverage($coverage)
    {
        $this->coverage = $this->merge($this->coverage, $coverage);
    }

    /**
     * @return array
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return mixed
     */
    private function merge($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (trim($key) === '-') {
                continue;
            }
            if (is_array($value)) {
                $array1[$key] = $this->merge(isset($array1[$key]) ? $array1[$key] : [], $value);
            } else if (!isset($array1[$key]) || $value > $array1[$key]) {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }
}
