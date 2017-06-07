<?php

namespace Gmo\Common\Config;

use Bolt\Collection\Bag;
use Gmo\Common\Dependency\DependencyResolver;
use GMO\Common\Exception\ConfigException;
use Gmo\Common\Exception\Dependency\CyclicDependencyException;
use Gmo\Common\Exception\Dependency\UnknownDependencyException;
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

        $envs = $this->sortEnvs($envs);

        // Merge in parent configs for each environment, specified with "_extends" key
        foreach ($envs as $name => $config) {
            // Skip "default" since this is the based for all
            if ($name === 'default') {
                continue;
            }

            $parent = $config->remove('_extends', 'default');
            $envs[$name] = $envs[$parent]->replaceRecursive($config);
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

    /**
     * Sort envs based on their "_extends" key.
     *
     * @param Bag $envs
     *
     * @throws ConfigException
     *
     * @return Bag
     */
    private function sortEnvs($envs)
    {
        $resolver = DependencyResolver::fromMap($envs, function (Bag $env, $key) {
            // Default is the base case and cannot depend upon anything.
            if ($key === 'default') {
                return [];
            }

            $parent = $env->get('_extends', 'default');

            return [$parent];
        });

        try {
            return $resolver->sort()->mutable();
        } catch (CyclicDependencyException $e) {
            $e->setItemName('environments');
            throw $e;
        } catch (UnknownDependencyException $e) {
            $message = sprintf(
                "Config environment '%s' cannot extend '%s' environment because it does not exist.",
                $e->getItem(),
                $e->getDependency()
            );
            throw new ConfigException($message, 0, $e);
        }
    }
}
