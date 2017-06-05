<?php

namespace Gmo\Common\Config;

use Bolt\Collection\Bag;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * This is a singleton implementation for ConfigBags.
 * This should be subclassed for each project and class parameters $PROJECT_DIR and $CONFIG_FILE redefined (if needed).
 *
 * There are magic method shortcuts (listed below for IDE completion) that forward static calls to ConfigBag.
 * The ConfigBag can also be accessed directly with static::getConfig().
 *
 * Helper methods for current environment and resolving paths are also provided.
 *
 * Using this class is an easy migration from deprecated {@see AbstractConfig} and {@see EnviornmentAwareConfig}.
 * However, we probably want to move away from the singleton pattern for Config. It is really not needed with Silex.
 *
 * @method static mixed get($path, $default = null)
 * @method static Bag getBag($path, $default = null)
 * @method static bool getBool($path, $default = null)
 * @method static string getPath($path, $default = null)
 * @method static void set($path, $value)
 * @method static ConfigBagInterface child($path, $cls = ConfigBagInterface::class)
 * @method static string getEnv()
 * @method static void setEnv($env)
 * @method static ConfigBagInterface withEnv($env)
 */
class Config
{
    /**
     * Path to project directory (relative to declared class's directory).
     *
     * Redefine this in your subclass if needed.
     *
     * @return string
     */
    protected $PROJECT_DIR = '..';

    /**
     * Path to main yaml config file (relative to project directory).
     *
     * Redefine this in your subclass if needed.
     *
     * @return string
     */
    protected $CONFIG_FILE = 'config.yml';

    /** @var string */
    private $projectPath;
    /** @var ConfigBagInterface */
    private $config;

    /**
     * Don't call directly, use {@see getInstance} instead.
     *
     * @internal
     *
     * @var Config[]
     */
    protected static $instances = [];

    /**
     * Returns whether the current environment is production.
     *
     * @return bool
     */
    public static function isProduction()
    {
        return static::getEnv() === 'production';
    }

    /**
     * Returns whether the current environment is staging.
     *
     * @return bool
     */
    public static function isStaging()
    {
        $env = static::getEnv();
        return $env === 'staging' || $env === 'stage';
    }

    /**
     * Returns whether the current environment is development.
     *
     * @return bool
     */
    public static function isDevelopment()
    {
        return static::getEnv() === 'development';
    }

    /**
     * Returns whether the current environment is CI.
     *
     * @return bool
     */
    public static function isCI()
    {
        return (bool) getenv('CI');
    }

    /**
     * Resolves the given path to the project root.
     *
     * @param string $path
     *
     * @return string
     */
    final public static function absPath($path)
    {
        return Path::makeAbsolute($path, static::getInstance()->getProjectPath());
    }

    /**
     * Return the ConfigBag for the called class.
     *
     * @return ConfigBagInterface
     */
    public static function getConfig()
    {
        return static::getInstance()->config;
    }

    /**
     * Return the singleton instance for the called class.
     *
     * @return static
     */
    final public static function getInstance()
    {
        $cls = get_called_class();
        if (isset(static::$instances[$cls])) {
            return static::$instances[$cls];
        }

        if ($cls === self::class) {
            throw new \LogicException("$cls cannot be used directly. Please subclass in your project.");
        }

        return static::$instances[$cls] = static::create();
    }

    /**
     * Forward all undefined methods to called class' instance of the ConfigBag.
     *
     * Don't call directly.
     *
     * @internal
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $config = static::getConfig();
        Assert::methodExists($config, $name);

        return call_user_func_array([$config, $name], $arguments);
    }

    /**
     * Create an instance of Config. Override if you need to change how ConfigBags are created.
     *
     * @return static
     */
    protected static function create()
    {
        $config = new static();

        $config->config = (new ConfigFactory())->create($config->getProjectPath(), $config->CONFIG_FILE);

        return $config;
    }

    /**
     * Return the absolute project root path.
     *
     * This is found by taking the class' $PROJECT_DIR property and resolving it onto
     * the directory that the called class is located in.
     *
     * @return string
     */
    protected function getProjectPath()
    {
        if (!$this->projectPath) {
            $cls = new \ReflectionClass(get_called_class());
            $baseDir = dirname($cls->getFileName());
            $this->projectPath = Path::makeAbsolute($this->PROJECT_DIR, $baseDir);
        }

        return $this->projectPath;
    }

    /**
     * Private constructor. Children cannot accept parameters.
     */
    private function __construct()
    {
    }
}
