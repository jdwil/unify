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

declare(strict_types=1);

namespace JDWil\Unify\Command;

use JDWil\Unify\TestRunner\Php\XDebugSession;
use JDWil\Unify\Parser\FileTypeChecker;
use JDWil\Unify\Parser\ParserFactory;
use JDWil\Unify\TestRunner\TestPlan;
use JDWil\Unify\TestRunner\TestRunner;
use JDWil\Unify\Trace\TraceParser;
use JDWil\Unify\Validation\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class RunCommand extends AbstractUnifyCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('run')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to test.')
            ->addOption(
                'coverage',
                null,
                InputOption::VALUE_NONE,
                'Enable code coverage generation. This will slow down your tests immensely.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $factory = $this->getContainer()->get('parser_factory');
        $testRunner = $this->getContainer()->get('test_runner_factory')->create($output);

        if ($file = $input->getArgument('file')) {
            $file = realpath($file);
            $parser = $factory->createParser($file);
            $parser->parse($file);
            foreach ($parser->getTestPlans() as $testPlan) {
                $testRunner->addTestPlan($testPlan);
            }
            $testRunner->execute($input->getOption('coverage'));

            exit($testRunner->statusCode());
        } else {
            /** @var Finder $find */
            $find = $this->getContainer()->get('finder');
            $find = $find::create();
            $find->files()->in(getcwd())->exclude(['vendor', 'src']);
            $find = $this->addFinderNames($find);
            foreach ($find as $file) {
                $this->debug($file->getPathname());
                $parser = $factory->createParser($file->getPathname());
                $parser->parse($file->getPathname());
                foreach ($parser->getTestPlans() as $testPlan) {
                    $testRunner->addTestPlan($testPlan);
                }
            }

            $testRunner->execute($input->getOption('coverage'));
        }
    }

    protected function addFinderNames(Finder $find)
    {
        $find
            ->name('*.md')
            ->name('*.markdown')
            ->name('*.mdown')
            ->name('*.mkdn')
            ->name('*.php')
        ;

        return $find;
    }

    private function debug($message)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln(sprintf('<info>%s</info>', $message));
        }
    }
}
