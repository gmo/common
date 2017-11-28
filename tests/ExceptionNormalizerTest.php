<?php

namespace Gmo\Common\Tests;

use Gmo\Common\ExceptionNormalizer;
use PHPUnit\Framework\TestCase;

class ExceptionNormalizerTest extends TestCase
{
    public function testShortenTrace()
    {
        $main = [
            ['file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/AbstractPostgreSQLDriver.php', 'line' => 95],
            ['class' => 'Doctrine\DBAL\Driver\AbstractPostgreSQLDriver', 'type' => '->', 'function' => 'convertException', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/DBALException.php', 'line' => 176],
            ['class' => 'Doctrine\DBAL\DBALException', 'type' => '::', 'function' => 'wrapException', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/DBALException.php', 'line' => 161],
            ['class' => 'Doctrine\DBAL\DBALException', 'type' => '::', 'function' => 'driverException', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOPgSql/Driver.php', 'line' => 66],
            ['class' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver', 'type' => '->', 'function' => 'connect', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Connection.php', 'line' => 372],
            ['class' => 'Doctrine\DBAL\Connection', 'type' => '->', 'function' => 'connect', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Connection.php', 'line' => 1435],
            ['class' => 'Doctrine\DBAL\Connection', 'type' => '->', 'function' => 'getWrappedConnection', 'file' => 'src/Provider/PhinxServiceProvider.php', 'line' => 57],
            ['class' => 'Gmo\Stats\Provider\PhinxServiceProvider', 'type' => '->', 'function' => 'Gmo\Stats\Provider\{closure}', 'file' => 'vendor/pimple/pimple/src/Pimple/Container.php', 'line' => 118],
            ['class' => 'Pimple\Container', 'type' => '->', 'function' => 'offsetGet', 'file' => 'vendor/gmo/web/src/Provider/ProxyServiceProvider.php', 'line' => 66],
            ['class' => 'Gmo\Web\Provider\ProxyServiceProvider', 'type' => '->', 'function' => 'Gmo\Web\Provider\{closure}', 'file' => 'var/proxies/ProxyManagerGeneratedProxy__PM__PhinxConfigConfigGenerated58b24f367e3cf5c9ed08c7709eb8e1bc.php', 'line' => 92],
            ['class' => 'Closure', 'type' => '->', 'function' => '__invoke', 'file' => 'var/proxies/ProxyManagerGeneratedProxy__PM__PhinxConfigConfigGenerated58b24f367e3cf5c9ed08c7709eb8e1bc.php', 'line' => 92],
            ['class' => 'ProxyManagerGeneratedProxy\__PM__\Phinx\Config\Config\Generated58b24f367e3cf5c9ed08c7709eb8e1bc', 'type' => '->', 'function' => 'getMigrationPaths', 'file' => 'vendor/robmorgan/phinx/src/Phinx/Console/Command/AbstractCommand.php', 'line' => 99],
            ['class' => 'Phinx\Console\Command\AbstractCommand', 'type' => '->', 'function' => 'bootstrap', 'file' => 'vendor/robmorgan/phinx/src/Phinx/Console/Command/Migrate.php', 'line' => 73],
            ['class' => 'Phinx\Console\Command\Migrate', 'type' => '->', 'function' => 'execute', 'file' => 'vendor/symfony/console/Command/Command.php', 'line' => 240],
            ['class' => 'Symfony\Component\Console\Command\Command', 'type' => '->', 'function' => 'run', 'file' => 'vendor/symfony/console/Application.php', 'line' => 876],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'doRunCommand', 'file' => 'vendor/symfony/console/Application.php', 'line' => 216],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'doRun', 'file' => 'vendor/symfony/console/Application.php', 'line' => 122],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'run', 'file' => 'bin/console', 'line' => 16],
        ];

        $sub1 = [
            ['file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOConnection.php', 'line' => 47],
            ['class' => 'Doctrine\DBAL\Driver\PDOConnection', 'type' => '->', 'function' => '__construct', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOPgSql/Driver.php', 'line' => 45],
            ['class' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver', 'type' => '->', 'function' => 'connect', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Connection.php', 'line' => 372],
            ['class' => 'Doctrine\DBAL\Connection', 'type' => '->', 'function' => 'connect', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Connection.php', 'line' => 1435],
            ['class' => 'Doctrine\DBAL\Connection', 'type' => '->', 'function' => 'getWrappedConnection', 'file' => 'src/Provider/PhinxServiceProvider.php', 'line' => 57],
            ['class' => 'Gmo\Stats\Provider\PhinxServiceProvider', 'type' => '->', 'function' => 'Gmo\Stats\Provider\{closure}', 'file' => 'vendor/pimple/pimple/src/Pimple/Container.php', 'line' => 118],
            ['class' => 'Pimple\Container', 'type' => '->', 'function' => 'offsetGet', 'file' => 'vendor/gmo/web/src/Provider/ProxyServiceProvider.php', 'line' => 66],
            ['class' => 'Gmo\Web\Provider\ProxyServiceProvider', 'type' => '->', 'function' => 'Gmo\Web\Provider\{closure}', 'file' => 'var/proxies/ProxyManagerGeneratedProxy__PM__PhinxConfigConfigGenerated58b24f367e3cf5c9ed08c7709eb8e1bc.php', 'line' => 92],
            ['class' => 'Closure', 'type' => '->', 'function' => '__invoke', 'file' => 'var/proxies/ProxyManagerGeneratedProxy__PM__PhinxConfigConfigGenerated58b24f367e3cf5c9ed08c7709eb8e1bc.php', 'line' => 92],
            ['class' => 'ProxyManagerGeneratedProxy\__PM__\Phinx\Config\Config\Generated58b24f367e3cf5c9ed08c7709eb8e1bc', 'type' => '->', 'function' => 'getMigrationPaths', 'file' => 'vendor/robmorgan/phinx/src/Phinx/Console/Command/AbstractCommand.php', 'line' => 99],
            ['class' => 'Phinx\Console\Command\AbstractCommand', 'type' => '->', 'function' => 'bootstrap', 'file' => 'vendor/robmorgan/phinx/src/Phinx/Console/Command/Migrate.php', 'line' => 73],
            ['class' => 'Phinx\Console\Command\Migrate', 'type' => '->', 'function' => 'execute', 'file' => 'vendor/symfony/console/Command/Command.php', 'line' => 240],
            ['class' => 'Symfony\Component\Console\Command\Command', 'type' => '->', 'function' => 'run', 'file' => 'vendor/symfony/console/Application.php', 'line' => 876],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'doRunCommand', 'file' => 'vendor/symfony/console/Application.php', 'line' => 216],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'doRun', 'file' => 'vendor/symfony/console/Application.php', 'line' => 122],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'run', 'file' => 'bin/console', 'line' => 16],
        ];
        $expectedSub1 = [
            ['file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOConnection.php', 'line' => 47],
            ['class' => 'Doctrine\DBAL\Driver\PDOConnection', 'type' => '->', 'function' => '__construct', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOPgSql/Driver.php', 'line' => 45],
            ['removed' => 14],
        ];

        $sub2 = [
            ['file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOConnection.php', 'line' => 43],
            ['class' => 'PDO', 'type' => '->', 'function' => '__construct', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOConnection.php', 'line' => 43],
            ['class' => 'Doctrine\DBAL\Driver\PDOConnection', 'type' => '->', 'function' => '__construct', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOPgSql/Driver.php', 'line' => 45],
            ['class' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver', 'type' => '->', 'function' => 'connect', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Connection.php', 'line' => 372],
            ['class' => 'Doctrine\DBAL\Connection', 'type' => '->', 'function' => 'connect', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Connection.php', 'line' => 1435],
            ['class' => 'Doctrine\DBAL\Connection', 'type' => '->', 'function' => 'getWrappedConnection', 'file' => 'src/Provider/PhinxServiceProvider.php', 'line' => 57],
            ['class' => 'Gmo\Stats\Provider\PhinxServiceProvider', 'type' => '->', 'function' => 'Gmo\Stats\Provider\{closure}', 'file' => 'vendor/pimple/pimple/src/Pimple/Container.php', 'line' => 118],
            ['class' => 'Pimple\Container', 'type' => '->', 'function' => 'offsetGet', 'file' => 'vendor/gmo/web/src/Provider/ProxyServiceProvider.php', 'line' => 66],
            ['class' => 'Gmo\Web\Provider\ProxyServiceProvider', 'type' => '->', 'function' => 'Gmo\Web\Provider\{closure}', 'file' => 'var/proxies/ProxyManagerGeneratedProxy__PM__PhinxConfigConfigGenerated58b24f367e3cf5c9ed08c7709eb8e1bc.php', 'line' => 92],
            ['class' => 'Closure', 'type' => '->', 'function' => '__invoke', 'file' => 'var/proxies/ProxyManagerGeneratedProxy__PM__PhinxConfigConfigGenerated58b24f367e3cf5c9ed08c7709eb8e1bc.php', 'line' => 92],
            ['class' => 'ProxyManagerGeneratedProxy\__PM__\Phinx\Config\Config\Generated58b24f367e3cf5c9ed08c7709eb8e1bc', 'type' => '->', 'function' => 'getMigrationPaths', 'file' => 'vendor/robmorgan/phinx/src/Phinx/Console/Command/AbstractCommand.php', 'line' => 99],
            ['class' => 'Phinx\Console\Command\AbstractCommand', 'type' => '->', 'function' => 'bootstrap', 'file' => 'vendor/robmorgan/phinx/src/Phinx/Console/Command/Migrate.php', 'line' => 73],
            ['class' => 'Phinx\Console\Command\Migrate', 'type' => '->', 'function' => 'execute', 'file' => 'vendor/symfony/console/Command/Command.php', 'line' => 240],
            ['class' => 'Symfony\Component\Console\Command\Command', 'type' => '->', 'function' => 'run', 'file' => 'vendor/symfony/console/Application.php', 'line' => 876],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'doRunCommand', 'file' => 'vendor/symfony/console/Application.php', 'line' => 216],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'doRun', 'file' => 'vendor/symfony/console/Application.php', 'line' => 122],
            ['class' => 'Symfony\Component\Console\Application', 'type' => '->', 'function' => 'run', 'file' => 'bin/console', 'line' => 16],
        ];
        $expectedSub2 = [
            ['file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOConnection.php', 'line' => 43],
            ['class' => 'PDO', 'type' => '->', 'function' => '__construct', 'file' => 'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOConnection.php', 'line' => 43],
            ['removed' => 15],
        ];

        $normalizer = new ExceptionNormalizer(null);

        $this->assertEquals($expectedSub1, $normalizer->shortenTrace($main, $sub1));
        $this->assertEquals($expectedSub2, $normalizer->shortenTrace($sub1, $sub2));
    }
}
