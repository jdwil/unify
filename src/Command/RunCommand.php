<?php
declare(strict_types=1);

namespace JDWil\Unify\Command;

use JDWil\Unify\Debugger\DebugSession;
use JDWil\Unify\Parser\FileTypeChecker;
use JDWil\Unify\Parser\ParserFactory;
use JDWil\Unify\TestRunner\TestRunner;
use JDWil\Unify\Trace\TraceParser;
use JDWil\Unify\Validation\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('run')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to test.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($file = $input->getArgument('file')) {
            $file = realpath($file);
            $typeChecker = new FileTypeChecker();
            $factory = new ParserFactory($typeChecker);
            $parser = $factory->createParser($file);
            $parser->parse();

            $session = new DebugSession('127.0.0.1', 9000, $output);
            $assertions = $session->debugFile($file, $parser->getAssertions());
            print_r($assertions);
        }
    }
}
