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

use JDWil\Unify\Debugger\DebugSession;
use JDWil\Unify\Parser\FileTypeChecker;
use JDWil\Unify\Parser\ParserFactory;
use JDWil\Unify\TestRunner\TestPlan;
use JDWil\Unify\TestRunner\TestRunner;
use JDWil\Unify\Trace\TraceParser;
use JDWil\Unify\Validation\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractUnifyCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('run')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to test.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($file = $input->getArgument('file')) {
            $file = realpath($file);
            $factory = $this->getContainer()->get('parser_factory');
            $parser = $factory->createParser($file);
            $parser->parse();
            $testPlans = $parser->getTestPlans();

            $testRunner = $this->getContainer()->get('test_runner_factory')->create($output);
            foreach ($testPlans as $testPlan) {
                $testRunner->addTestPlan($testPlan);
            }
            $testRunner->execute();

            exit($testRunner->statusCode());
        }
    }
}
