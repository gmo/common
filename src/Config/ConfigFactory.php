<?php

namespace Gmo\Common\Config;

use Bolt\Collection\Bag;
use GMO\Common\Exception\ConfigException;
use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

/**
 * Factory for creating ConfigBags.
 */
class ConfigFactory
{
    /**
     * Create a ConfigBag.
     *
     * @param string $rootPath
     * @param string $configFilePath
     *
     * @return ConfigBagInterface
     */
    public function create($rootPath, $configFilePath)
    {
        $file = $this->resolvePath($rootPath, $configFilePath);

        $envs = $this->parseMainFile($file);
        $envs = $this->mergeExternalFiles($file, $envs);

        return ConfigBag::root(
            $envs,
            $this->getEnvironment(),
            $rootPath
        );
    }

    /**
     * Resolve config file path and ensure it exists.
     *
     * @param string $rootPath
     * @param string $configFilePath
     *
     * @throws ConfigException
     *
     * @return string
     */
    protected function resolvePath($rootPath, $configFilePath)
    {
        $file = Path::makeAbsolute($configFilePath, $rootPath);

        if (!file_exists($file)) {
            throw new ConfigException("Config file doesn't exist.");
        }

        return $file;
    }

    /**
     * Parse main config file.
     *
     * @param string $file
     *
     * @throws ConfigException If env extends an env not parsed yet.
     *
     * @return Bag|Bag[]
     */
    protected function parseMainFile($file)
    {
        $envs = $this->parse($file);

        if (!$envs->has('default')) {
            $envs['default'] = new Bag();
        }

        $parsed = [
            'default' => true,
        ];

        // Merge in parent configs for each environment, specified with "_extends" key
        foreach ($envs as $name => $config) {
            // Skip "default" since this is the based for all
            if ($name === 'default') {
                continue;
            }

            $parent = $config->remove('_extends', 'default');
            if (!isset($parsed[$parent])) {
                throw new ConfigException("Move '$name' below '$parent' environment.");
            }
            $envs[$name] = $envs[$parent]->replaceRecursive($config);
            $parsed[$name] = true;
        }

        return $envs;
    }

    /**
     * Merge in separate environment specific files.
     *
     * @param string $file
     * @param Bag|Bag[] $envs
     *
     * @return Bag|Bag[]
     */
    protected function mergeExternalFiles($file, Bag $envs)
    {
        $extPos = strrpos($file, '.');
        $pathTemplate = substr_replace($file, '.%s.', $extPos, 1);
        foreach ($envs as $name => $config) {
            // config.yml -> config.production.yml
            $envFile = sprintf($pathTemplate, $name);
            if (!is_readable($envFile)) {
                continue;
            }
            $envConfig = $this->parse($envFile);
            $envs[$name] = $config->replaceRecursive($envConfig);
        }

        return $envs;
    }

    /**
     * @param string $file
     *
     * @return Bag|Bag[]
     */
    protected function parse($file)
    {
        $data = Yaml::parse(file_get_contents($file));

        return Bag::fromRecursive($data);
    }

    /**
     * @throws ConfigException
     *
     * @return string
     */
    protected function getEnvironment()
    {
        $env = getenv('PHP_ENV');
        if ($env === false) {
            throw new ConfigException('"PHP_ENV" is not set');
        }

        return $env;
    }
}
