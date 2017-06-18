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

            /*
            $runner = new TestRunner();
            $assertions = $parser->getAssertions();
            $traceFile = $runner->run($file, $parser->getAssertions());
            $parser = new TraceParser();
            $trace = $parser->parseFile($traceFile);
            $validator = new Validator();
            $validator->validateTrace($trace, $assertions);
            */

            $session = new DebugSession('127.0.0.1', 9000);
            $plan = $session->debugFile($file, $parser->getAssertions());
            //print_r($plan->getSteps());

            die();
        }
        $examples = __DIR__ . '/../../examples';
        $code = file(sprintf('%s/test.php', $examples));

        for ($lineNumber = 0; $lineNumber < count($code); $lineNumber++) {
            $line = $code[$lineNumber];
            if (preg_match('#//.*$#', $line, $m)) {
                if ($assertion = $this->getAssertion($m[0], trim(str_replace($m[0], '', $line)))) {
                    $lineNumber++;
                    array_splice($code, $lineNumber, 0, $assertion . "\n");
                }
            }
        }

        array_shift($code);
        $code = implode('', $code);
        echo $code;
        eval($code);
    }

    private function getAssertion(string $code, string $line)
    {
        $code = trim(str_replace('//', '', $code));
        if (preg_match('/^[\'\"]?[a-zA-Z0-9_\.]+[\'\"]?$/', $code, $m)) {
            return sprintf('if (!((%s) == %s)) { throw new \Exception(\'Expression does not equal %s\'); }', str_replace(';', '', $line), $m[0], $m[0]);
        }

        if (preg_match('/(\$[a-zA-Z]\w*)\s*([=><]=?=?)\s*([\'\"]?\w[\'\"]?)/', $code, $m)) {
            if (substr_count($m[0], '=') === 1) {
                $m[0] = str_replace('=', '==', $m[0]);
            }
            return sprintf('if (!(%s)) { throw new \Exception(\'%s does not equal %s\'); }', $m[0], $m[1], $m[3]);
        }
    }
}
