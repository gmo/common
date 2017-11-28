<?php

namespace Gmo\Common\Console;

use Bolt\Common\Json;
use Gmo\Common\ExceptionNormalizer;
use Psr\Container\ContainerInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base Application that adds:
 *   - Auto versioning from git or composer
 *   - Added optional ContainerHelper to default helpers
 *   - Added optional CompletionCommand to default commands
 *   - Tweaked exception rendering
 */
class Application extends \Symfony\Component\Console\Application
{
    /** @var ContainerInterface|null */
    private $container;
    /** @var string|null */
    private $projectDir;
    /** @var string|null */
    private $packageName;

    /**
     * Constructor.
     *
     * @param string                  $name      The name of the application
     * @param string|null             $version   The version of the application
     * @param ContainerInterface|null $container The dependency container
     */
    public function __construct(string $name = 'UNKNOWN', string $version = null, ContainerInterface $container = null)
    {
        $this->container = $container;

        parent::__construct($name, $version);
    }

    public function getProjectDirectory(): ?string
    {
        return $this->projectDir;
    }

    /**
     * Set the project directory. Used in determining version.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setProjectDirectory(string $dir): Application
    {
        if (is_dir($dir)) {
            $this->projectDir = $dir;
        }

        return $this;
    }

    /**
     * Set the composer package name. Used in determining version.
     *
     * @param string $packageName
     *
     * @return $this
     */
    public function setPackageName(string $packageName): Application
    {
        $this->packageName = $packageName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        if (parent::getVersion() === null && $this->projectDir) {
            if ($this->packageName) {
                $version = $this->findComposerVersion($this->packageName, $this->projectDir);
            } else {
                $version = $this->findGitVersion($this->projectDir);
            }

            $this->setVersion($version ?: 'UNKNOWN');
        }

        return parent::getVersion();
    }

    public function renderException(\Exception $e, OutputInterface $output)
    {
        $io = new SymfonyStyle(new ArrayInput([]), $output);

        $normalizer = new ExceptionNormalizer($this->getProjectDirectory());

        $prevTrace = null;
        do {
            $this->renderExceptionTitle($e, $io);

            if ($io->isVerbose()) {
                $io->writeln('<comment>Exception trace:</comment>');

                $fullTrace = $normalizer->normalizeTrace($e);
                $trace = $prevTrace !== null ? $normalizer->shortenTrace($prevTrace, $fullTrace) : $fullTrace;
                $prevTrace = $fullTrace;

                $this->renderExceptionTrace($trace, $io);

                $io->newLine();
            }
        } while ($e = $e->getPrevious());
    }

    private function renderExceptionTitle(\Exception $e, SymfonyStyle $io)
    {
        $message = sprintf(
            "[%s%s]\n%s",
            get_class($e),
            $io->isVerbose() && 0 !== ($code = $e->getCode()) ? " ($code)" : '',
            trim($e->getMessage())
        );

        $io->block($message, null, 'fg=white;bg=red', ' ', true);
    }

    private function renderExceptionTrace(array $trace, SymfonyStyle $io)
    {
        foreach ($trace as $i => $frame) {
            if (isset($frame['removed'])) {
                $io->writeln(sprintf(' ... %s more', $frame['removed']));
                continue;
            }

            $class = $frame['class'] ?? '';
            $type = $frame['type'] ?? '';
            $function = $frame['function'] ?? '';
            $file = $frame['file'] ?? 'n/a';
            $line = $frame['line'] ?? 'n/a';

            $message = $i === 0
                ? ' Thrown at <info>%4$s:%5$s</info>'
                : ' %s%s%s() at <info>%s:%s</info>';
            $io->writeln(sprintf($message, $class, $type, $function, $file, $line));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        if (class_exists(CompletionCommand::class)) {
            $command = new CompletionCommand();
            if (method_exists($command, 'setHidden')) {
                $command->setHidden(true);
            }
            $commands[] = $command;
        }

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        $helpers = parent::getDefaultHelperSet();

        if ($this->container) {
            $helpers->set(new Helper\ContainerHelper($this->container));
        }

        return $helpers;
    }

    /**
     * Reads the composer lock file based on the project directory
     * and parses the version for the specified package name.
     *
     * @param string $packageName
     * @param string $projectDir
     *
     * @return string|null
     */
    private function findComposerVersion(string $packageName, string $projectDir): ?string
    {
        // A library app, but being ran from source so use git version.
        if (file_exists("$projectDir/vendor")) {
            return $this->findGitVersion($projectDir);
        }

        $composerFile = "$projectDir/../../../composer.lock";
        if (!file_exists($composerFile)) {
            return null;
        }
        $composer = Json::parse(file_get_contents($composerFile));

        foreach (['packages', 'packages-dev'] as $group) {
            foreach ($composer[$group] as $package) {
                if ($package['name'] === $packageName) {
                    return ltrim($package['version'] ?? null, 'v');
                }
            }
        }

        return null;
    }

    /**
     * Returns the current git branch name and revision or null if git repo cannot be found.
     *
     * @param string $projectDir
     *
     * @return string|null
     */
    private function findGitVersion(string $projectDir): ?string
    {
        $branch = static::revParse('--abbrev-ref HEAD', $projectDir);

        if (empty($branch)) {
            return null;
        }

        return $branch . ' ' . static::revParse('--short HEAD', $projectDir);
    }

    /**
     * Runs `git rev-parse` in the $projectDir with the given $args.
     *
     * @param string $args
     * @param string $projectDir
     *
     * @return string
     */
    private function revParse(string $args, string $projectDir): string
    {
        return trim(shell_exec("cd $projectDir && git rev-parse $args 2> /dev/null"));
    }
}
