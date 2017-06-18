<?php
declare(strict_types=1);

namespace JDWil\Unify\Trace;

class TraceParser
{
    const TYPE_ENTER_SCOPE = '->';
    const TYPE_ASSIGNMENT = '=>';
    const TYPE_RETURN_VALUE = '>=>';

    private $scope;

    /**
     * @var FunctionCall
     */
    private $lastFunctionCall;

    public function __construct()
    {
        $this->scope = [];
    }

    public function parseFile(string $filePath)
    {
        return $this->parse(file($filePath));
    }

    private function parse(array $lines)
    {
        $trace = new Trace();

        foreach ($lines as $line) {
            if (strpos($line, '>') === false) {
                continue;
            }

            $line = substr($line, 24);

            if (!preg_match('#(>?[=-]>)\s*([^/]*)(/?[^\s:]*):?(\d*)$#', trim($line), $m)) {
                throw new \Exception('Do not know how to handle line ' . $line);
            }

            list($full, $type, $expression, $file, $line) = $m;

            switch ($type) {
                case self::TYPE_ENTER_SCOPE:
                    if (strpos($expression, '{main}') !== false) {
                        $this->scope[] = 'main';
                    } else {
                        if (!preg_match('#([a-zA-Z][a-zA-Z0-9_>-]*)\(([^\)]*)\)#', $expression, $m)) {
                            throw new \Exception('foo');
                        }
                        list($full, $functionName, $params) = $m;
                        $list = explode(',', $params);
                        $params = [];
                        foreach ($list as $param) {
                            list($var, $value) = explode(' = ', $param);
                            $params[] = new Parameter($var, $value);
                        }

                        $this->lastFunctionCall = new FunctionCall(
                            $functionName,
                            $file,
                            (int) $line,
                            $params
                        );

                        $trace->addFunctionCall($this->lastFunctionCall);

                        $this->scope[] = $functionName;
                    }
                    break;

                case self::TYPE_ASSIGNMENT:
                    $pieces = explode(' = ', trim($expression));
                    $var = array_shift($pieces);
                    $value = implode(' = ', $pieces);
                    $trace->addAssignment(new Assignment($var, $value, $file, (int) $line));
                    break;

                case self::TYPE_RETURN_VALUE:
                    $this->lastFunctionCall->setReturn($expression);
                    break;
            }
        }

        return $trace;
    }
}
