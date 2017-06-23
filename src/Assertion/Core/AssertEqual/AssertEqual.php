<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

namespace JDWil\Unify\Assertion\Core\AssertEqual;

use JDWil\Unify\Assertion\AbstractAssertion;

/**
 * Class AssertEqual
 */
class AssertEqual extends AbstractAssertion
{
    /**
     * @var string
     */
    private $variable;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var mixed
     */
    private $internalValue;

    /**
     * AssertEqual constructor.
     * @param string $variable
     * @param $value
     * @param int $line
     * @param int $iteration
     * @param string $file
     */
    public function __construct($variable, $value, $line, $iteration, $file)
    {
        $this->variable = $variable;
        $this->value = $value;

        parent::__construct($line, $file, $iteration);
    }

    /**
     * The returned command must contain "-i %d"
     *
     * @return array
     */
    public function getDebuggerCommands()
    {
        $ret = [
            "context_get -i %d -d 0 -c 0\0"
        ];

        if ($this->valueNeedsEvaluation()) {
            $ret[] = sprintf(
                "eval -i %%d -- %s\0",
                base64_encode(sprintf('%s;', $this->fullyQualifyClassConstant($this->value)))
            );
        }

        return $ret;
    }

    /**
     * @param \DOMElement $response
     * @param int $responseNumber
     */
    public function assert(\DOMElement $response, $responseNumber = 1)
    {
        if ($responseNumber === 1) {
            /** @var \DOMElement $child */
            foreach ($response->childNodes as $child) {
                if ($child->getAttribute('name') === $this->variable) {
                    if ($this->valueNeedsEvaluation()) {
                        $this->internalValue = $child->nodeValue;
                    } else {
                        $this->result = $child->nodeValue === $this->value;
                    }
                }
            }
        } else {
            $value = $response->firstChild->nodeValue;
            $this->result = $value === $this->internalValue;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Assert %s equals %s.', $this->variable, (string) $this->value);
    }

    /**
     * @return bool
     */
    private function valueNeedsEvaluation()
    {
        return strpos($this->value, '::') !== false;
    }
}
