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

namespace JDWil\Unify\Parser\Unify\PHP;

use JDWil\Unify\ValueObject\PHPContext;
use JDWil\Unify\Parser\Unify\UnifyParserInterface;

/**
 * Class PHPUnifyParserPipeline
 */
class PHPUnifyParserPipeline
{
    /**
     * @var PHPParserInterface[]
     */
    private $parsers;

    /**
     * @var PHPContext
     */
    private $context;

    /**
     * UnifyParserPipeline constructor.
     * @param PHPParserInterface[] $parsers
     */
    public function __construct($parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * @param PHPContext $context
     */
    public function setContext(PHPContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param array $tokens
     * @return array|false
     */
    public function handle($tokens)
    {
        foreach ($this->parsers as $parser) {
            $parser->initialize($tokens, $this->context);
            if ($result = $parser->parse()) {
                return $result;
            }
        }

        return false;
    }

    /**
     * @param UnifyParserInterface $parser
     */
    public function addParser(UnifyParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }
}
