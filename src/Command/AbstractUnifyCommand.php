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

namespace JDWil\Unify\Command;

use JDWil\Unify\DependencyInjection\Configuration;
use JDWil\Unify\Exception\ConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ContainerAwareCommand
 */
abstract class AbstractUnifyCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    protected function configure()
    {
        $this
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to unify.yml')
        ;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param InputInterface $input
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    protected function parseConfig(InputInterface $input)
    {
        if ($configPath = $input->getOption('config')) {
            if (!$this->container->get('filesystem')->exists($configPath)) {
                throw new InvalidArgumentException(sprintf('%s does not exist.', $configPath));
            }
            $config = Yaml::parse(file_get_contents($configPath));
        } else if ($this->getContainer()->get('filesystem')->exists(__DIR__ . '/../../unify.yml')) {
            $config = Yaml::parse(file_get_contents(__DIR__ . '/../../unify.yml'));
        } else {
            $config = [];
        }

        $processor = new Processor();
        $configuration = new Configuration();
        $this->config = $processor->processConfiguration($configuration, [$config]);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->parseConfig($input);
        $this->container->setParameter('xdebug.host', $this->config['xdebug']['host']);
        $this->container->setParameter('xdebug.port', $this->config['xdebug']['port']);
        $this->container->setParameter('coverage.dir', $this->config['coverage']['dir']);

        // @todo revisit this. The paths here can get messed up when not running from the root dir.
        if (isset($this->config['autoload_path'])) {
            $this->container->setParameter('autoload_path', realpath($this->config['autoload_path']));
        } else {
            $this->findRootDirectory();
        }
    }

    protected function findRootDirectory()
    {
        if (null !== $this->rootDir) {
            $this->container->setParameter('autoload_path', sprintf('%s/vendor/autoload.php', $this->rootDir));
            return;
        }

        $directoryStack = [];
        $filesystem = $this->getContainer()->get('filesystem');
        $directory = __DIR__ .'/../..';
        while (realpath($directory) !== '/') {
            if ($filesystem->exists(realpath(sprintf('%s/vendor/autoload.php', $directory)))) {
                $directoryStack[] = $directory;
            }

            $directory = sprintf('%s/..', $directory);
        }

        if (!empty($directoryStack)) {
            $this->container->setParameter('autoload_path', sprintf('%s/vendor/autoload.php', realpath(array_pop($directoryStack))));
        } else {
            throw new ConfigurationException('Could not locate autoload.php');
        }
    }
}
