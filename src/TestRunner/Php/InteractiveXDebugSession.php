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

namespace JDWil\Unify\TestRunner\Php;

use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class InteractiveXDebugSession extends AbstractSession
{
    protected $file;
    protected $input;

    /**
     * @var QuestionHelper
     */
    protected $helper;
    protected $question;

    public function __construct(
        OutputInterface $output,
        InputInterface $input,
        QuestionHelper $helper,
        $host,
        $port,
        $file
    ) {
        parent::__construct($output, $host, $port);
        $this->file = $file;
        $this->input = $input;
        $this->helper = $helper;

        $this->question = new Question('Enter a command: ');
    }

    public function execute()
    {
        $this->bootSocketServer();

        $this->socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                $responses = $this->parseResponse($data);

                foreach ($responses as $xml) {
                    $this->output->writeln($xml);
                }
                $this->output->writeln('');

                $command = $this->promptUser();

                if (strpos($command, '--') !== false) {
                    list($command, $expression) = explode(' -- ', $command);
                    $command = sprintf('%s -i 1 -- %s', $command, $expression);
                } else {
                    $command = sprintf('%s -i 1', $command);
                }

                $connection->write($command . "\0");
            });
        });

        $this->loop->addTimer(0.001, function () {
            $command = $this->buildPhpCommand(false, $this->file);
            $process = new Process($command);
            $process->start();
        });

        $this->loop->run();
    }

    /**
     * @return string
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    public function promptUser()
    {
        $command = $this->helper->ask($this->input, $this->output, $this->question);

        if (strpos($command, '--') !== false) {
            list($command, $expression) = explode(' -- ', $command);
            $command = sprintf('%s -- %s', $command, base64_encode($expression));
        }

        return $command;
    }
}
