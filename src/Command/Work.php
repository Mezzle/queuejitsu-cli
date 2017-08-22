<?php
/**
 * Copyright (c) 2017 Martin Meredith
 * Copyright (c) 2017 Stickee Technology Limited
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace QueueJitsu\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Work extends Command
{
    /**
     * @var callable $worker_factory
     */
    private $worker_factory;

    /**
     * Work constructor.
     *
     * @param callable $worker_factory
     */
    public function __construct(callable $worker_factory)
    {
        parent::__construct('work');
        $this->worker_factory = $worker_factory;
    }

    /**
     * configure
     */
    protected function configure()
    {
        $this->setDescription('Starts Working');
        $this->setHelp('This command starts worker(s) to process the queue.');

        $this->addOption(
            'background',
            'b',
            InputOption::VALUE_NONE,
            'Run in the background'
        );

        $this->addOption(
            'workers',
            'w',
            InputOption::VALUE_REQUIRED,
            'Workers to run (when ran in background mode)',
            2
        );

        $this->addOption(
            'log-level',
            null,
            InputOption::VALUE_REQUIRED,
            'Log Level <comment>(Must be one of emergency, alert, critical, error, warning, notice, info, debug)</comment>',
            'info'
        );

        $this->addOption(
            'interval',
            'i',
            InputOption::VALUE_REQUIRED,
            'How long to wait before checking for new jobs when none are available (in seconds)',
            5
        );

        $this->addOption(
            'pidfile',
            null,
            InputOption::VALUE_REQUIRED,
            'Location of Pidfile'
        );

        $this->addArgument(
            'queues',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Queues to process (separate multiple queues with a space)',
            ['*']
        );
    }

    /**
     * execute
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('background')) {
            $this->workInBackground($input);

            return 0;
        }

        $this->workInForeground($input);

        return 0;
    }

    /**
     * workInBackground
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    protected function workInBackground(InputInterface $input): void
    {
        $worker_count = $input->getOption('workers');

        for ($i = 0; $i < $worker_count; ++$i) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                die(sprintf("Could not fork worker %d\n", $i));
            } elseif (!$pid) {
                $pidfile = $input->getOption('pidfile');
                if ($pidfile && $i == 0) {
                    $this->writePidFile($pidfile);
                }

                $worker_factory = $this->worker_factory;

                /** @var array $queues */
                $queues = $input->getArgument('queues');

                /** @var \QueueJitsu\Worker\Worker $worker */
                $worker = $worker_factory($queues);

                $worker($input->getOption('interval'));
                break;
            }
        }
    }

    /**
     * writePidFile
     *
     * @param string $pidfile
     * @param null $pid
     */
    private function writePidFile(string $pidfile, $pid = null)
    {
        if (is_null($pid)) {
            $pid = getmypid();
        }

        file_put_contents($pidfile, $pid) || die(sprintf('Could not write PID information to %s', $pidfile));
    }

    /**
     * workInForeground
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    protected function workInForeground(InputInterface $input): void
    {
        $pidfile = $input->getOption('pidfile');
        if ($pidfile) {
            $this->writePidFile($pidfile);
        }

        $worker_factory = $this->worker_factory;

        /** @var array $queues */
        $queues = $input->getArgument('queues');

        /** @var \QueueJitsu\Worker\Worker $worker */
        $worker = $worker_factory($queues);

        $worker($input->getOption('interval'));
    }
}
