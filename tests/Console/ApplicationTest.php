<?php

namespace Gmo\Common\Tests\Console;

use Gmo\Common\Console\Application;
use Gmo\Common\Console\Helper\ContainerHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use Psr\Container\ContainerInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Command\HelpCommand;

class ApplicationTest extends TestCase
{
    public function testContainer()
    {
        if (!class_exists(CompletionCommand::class)) {
            class_alias(HelpCommand::class, CompletionCommand::class);
        }

        $container = $this->createMock(ContainerInterface::class);
        $console = new Application('common', 'v2', $container);

        /** @var ContainerHelper $helper */
        $helper = $console->getHelperSet()->get('container');

        $this->assertInstanceOf(ContainerHelper::class, $helper);
        $this->assertSame($container, $helper->getContainer());
    }

    public function testVersionFromGit()
    {
        $console = new Application('common');
        $console->setProjectDirectory(__DIR__ . '/../..');

        $this->assertStringMatchesFormat('%s %s', $console->getVersion());
    }

    public function testVersionFromComposer()
    {
        $console = new Application('common');
        $console->setProjectDirectory(__DIR__ . '/../..');
        $console->setPackageName('phpunit/phpunit');
        $this->assertEquals(Version::id(), $console->getVersion());
    }

    public function testVersionFromGitBadDir()
    {
        $console = new Application('common');
        $console->setProjectDirectory(sys_get_temp_dir());
        $this->assertEquals('UNKNOWN', $console->getVersion());
    }

    public function testVersionFromComposerBadDir()
    {
        $console = new Application('common');
        $console->setProjectDirectory(__DIR__ . '/..');
        $console->setPackageName('phpunit/phpunit');
        $this->assertEquals('UNKNOWN', $console->getVersion());
    }

    public function testVersionFromComposerUnknownPackage()
    {
        $console = new Application('common');
        $console->setProjectDirectory(__DIR__ . '/../..');
        $console->setPackageName('derp');
        $this->assertEquals('UNKNOWN', $console->getVersion());
    }
}
