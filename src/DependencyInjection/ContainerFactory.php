<?php

namespace JDWil\Unify\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ContainerFactory
 */
class ContainerFactory
{
    /**
     * @return ContainerBuilder
     * @throws \Exception
     */
    public static function buildContainer()
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.yml');

        return $container;
    }
}
