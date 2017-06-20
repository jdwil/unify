<?php

namespace JDWil\Unify\Debugger;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DebugSessionFactory
 */
class DebugSessionFactory
{
    /**
     * @var string
     */
    private $xdebugHost;

    /**
     * @var int
     */
    private $xdebugPort;

    /**
     * DebugSessionFactory constructor.
     * @param string $xdebugHost
     * @param int $xdebugPort
     */
    public function __construct($xdebugHost, $xdebugPort)
    {
        $this->xdebugHost = $xdebugHost;
        $this->xdebugPort = $xdebugPort;
    }

    /**
     * @param OutputInterface $output
     * @return DebugSession
     */
    public function create(OutputInterface $output)
    {
        return new DebugSession($this->xdebugHost, $this->xdebugPort, $output);
    }
}
