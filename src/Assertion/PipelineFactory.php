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

namespace JDWil\Unify\Assertion;

use Symfony\Component\Finder\Finder;

/**
 * Class PipelineFactory
 */
class PipelineFactory
{
    /**
     * @var Pipeline
     */
    private static $PIPELINE;

    /**
     * @param Finder $finder
     * @return Pipeline
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public static function create(Finder $finder)
    {
        if (null !== static::$PIPELINE) {
            return static::$PIPELINE;
        }

        static::$PIPELINE = new Pipeline();

        $finder->files()->in(__DIR__)->name('*Parser.php');
        $fileNames = [];

        foreach ($finder as $file) {
            require_once $file->getPathname();
            $fileNames[] = $file->getPathname();
        }

        $declared = get_declared_classes();
        foreach ($declared as $className) {
            $reflectionClass = new \ReflectionClass($className);
            if (in_array($reflectionClass->getFileName(), $fileNames, true) &&
                !$reflectionClass->isAbstract()
            ) {
                /** @var AssertionParserInterface $matcher */
                $matcher = $reflectionClass->newInstance();
                static::$PIPELINE->addMatcher($matcher);
            }
        }

        return static::$PIPELINE;
    }
}
