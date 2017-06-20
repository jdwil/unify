<?php

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

        $finder->files()->in(__DIR__)->name('*Matcher.php');
        $fileNames = [];

        foreach ($finder as $file) {
            require_once $file->getPathname();
            $fileNames[] = $file->getPathname();
        }

        $declared = get_declared_classes();
        foreach ($declared as $className) {
            $reflectionClass = new \ReflectionClass($className);
            if (in_array($reflectionClass->getFileName(), $fileNames, true)) {
                /** @var AssertionMatcherInterface $matcher */
                $matcher = $reflectionClass->newInstance();
                static::$PIPELINE->addMatcher($matcher);
            }
        }

        return static::$PIPELINE;
    }
}
