<?php

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

        // @todo revisit this. The paths here can get messed up when not running from the root dir.
        if (isset($this->config['autoload_path'])) {
            $this->container->setParameter('autoload_path', realpath($this->config['autoload_path']));
        } else {
            $this->findRootDirectory();
        }
    }

    protected function findRootDirectory()
    {
        $found = true;
        $filesystem = $this->getContainer()->get('filesystem');
        $directory = __DIR__ .'/../..';
        while (!$filesystem->exists(sprintf('%s/vendor/autoload.php', $directory))) {
            echo "Not in " . sprintf("%s/composer.json\n", $directory);
            $directory = sprintf('%s/..', $directory);
            if (realpath($directory) == '/') {
                $found = false;
                break;
            }
        }

        if ($found) {
            $this->container->setParameter('autoload_path', sprintf('%s/vendor/autoload.php', realpath($directory)));
        } else {
            throw new ConfigurationException('Could not locate autoload.php');
        }
    }
}
