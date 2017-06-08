<?php

namespace Gmo\Common\Config;

use Bolt\Collection\Bag;
use GMO\Common\Exception\ConfigException;
use GMO\Common\Str;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * The default implementation of ConfigBagInterface.
 */
class ConfigBag implements EnvironmentAwareConfigBagInterface
{
    /** @var Bag|Bag[] */
    private $environments;
    /** @var string */
    private $envName;
    /** @var string */
    private $projectPath;
    /** @var string */
    private $prefix = '';

    /**
     * The constructor for ConfigBag.
     *
     * @param Bag|Bag[] $environments Configs for mapped to environment names
     * @param string    $envName      The current environment name
     * @param string    $projectPath  The project root path
     *
     * @return static
     */
    public static function root($environments, $envName, $projectPath)
    {
        $root = new static();
        $root->environments = $environments;
        $root->envName = $envName;
        $root->projectPath = $projectPath;

        $root->verifyEnv($envName);

        return $root;
    }

    /**
     * {@inheritdoc}
     */
    public function child(string $path, $cls = null)
    {
        $cls = $cls ?: self::class;
        if ($cls !== self::class) {
            Assert::subclassOf($cls, self::class);
        }

        $child = new $cls();
        $child->environments = $this->environments;
        $child->envName = $this->envName;
        $child->projectPath = $this->projectPath;
        $child->prefix = Path::join($this->prefix, $path);

        return $child;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path, $default = null)
    {
        $path = Path::join($this->prefix, $path);

        $config = $this->environments->get($this->envName) ?: $this->environments->get('default');

        if (!$config->hasPath($path)) {
            if (func_num_args() === 2) {
                return $default;
            }
            throw new ConfigException("Config value for key '$path' is missing.");
        }

        $value = $config->getPath($path, $default);

        if (!is_string($value) || strpos($value, '%') !== 0 || !Str::endsWith($value, '%')) {
            return $value;
        }

        $childEnv = substr($value, 1, -1);
        if (!$this->environments->has($childEnv)) {
            throw new ConfigException("Config does not contain the environment '$childEnv' requested by '$path'.");
        }

        $previousEnv = $this->envName;
        $this->envName = $childEnv;
        try {
            return $this->get(...func_get_args());
        } finally {
            $this->envName = $previousEnv;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBag(string $path, $default = null)
    {
        $value = $this->get(...func_get_args());

        return Bag::from($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBool(string $path, $default = null): bool
    {
        $value = $this->get(...func_get_args());

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(string $path, $default = null): string
    {
        $value = $this->get(...func_get_args());

        return Path::makeAbsolute($value, $this->projectPath);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $path, $value)
    {
        $path = Path::join($this->prefix, $path);
        $this->environments[$this->envName]->setPath($path, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnv(): string
    {
        return $this->envName;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnv(string $env)
    {
        $this->verifyEnv($env);
        $this->envName = $env;
    }

    /**
     * {@inheritdoc}
     */
    public function withEnv(string $env)
    {
        $this->verifyEnv($env);

        $copy = clone $this;
        $copy->envName = $env;

        return $copy;
    }

    /**
     * Private constructor. Subclasses cannot accept any parameters.
     */
    private function __construct()
    {
    }

    protected function verifyEnv(string $env)
    {
        if (!$this->environments->has($env)) {
            throw new ConfigException("Config does not contain the environment '$env'.");
        }
    }
}
