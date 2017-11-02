<?php

namespace Gmo\Common\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base Nagios monitoring command.
 *
 * Adds shortcuts for output messages and status/exit codes.
 */
abstract class NagiosCommand extends Command
{
    protected const STATUS_OK = 0;
    protected const STATUS_WARNING = 1;
    protected const STATUS_ERROR = 2;
    protected const STATUS_UNKNOWN = 3;

    /** @var OutputInterface */
    private $output;

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        if (!$output->getFormatter()->hasStyle('warning')) {
            $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('yellow'));
        }
        try {
            return parent::run($input, $output);
        } catch (\Exception $e) {
            return $this->unknown($e->getMessage());
        }
    }

    protected function ok(string $str, ...$params): int
    {
        $this->output->writeln(sprintf("<info>$str</info>", ...$params));

        return static::STATUS_OK;
    }

    protected function warning(string $str, ...$params): int
    {
        $this->output->writeln(sprintf("<warning>$str</warning>", ...$params));

        return static::STATUS_WARNING;
    }

    protected function error(string $str, ...$params): int
    {
        $this->output->writeln(sprintf("<error>$str</error>", ...$params));

        return static::STATUS_ERROR;
    }

    protected function unknown(string $str, ...$params): int
    {
        $this->output->writeln(sprintf("<error>$str</error>", ...$params));

        return static::STATUS_UNKNOWN;
    }
}
