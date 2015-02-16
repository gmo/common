<?php
namespace GMO\Common\Config;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Exception\ConfigException;
use GMO\Common\String;

/**
 * This config groups values by environment.
 * A "default" environment is required.
 * By default the selected environment is based on the environment variable "PHP_ENV".
 * If the config does not contain the selected environment the default is used.
 *
 * Example:
 *     default:
 *       section:
 *         test: hello
 *
 *     staging:
 *       section:
 *         test: world
 *
 *     development:
 *       section:
 *         test: %staging%
 *
 *     The result for "section > test" for all environments
 *       production: hello
 *       staging: world
 *       development: world
 */
abstract class EnvironmentAwareConfig extends AbstractConfig {

	public static function getEnvironment() {
		$env = getenv('PHP_ENV');
		if ($env === false) {
			throw new ConfigException('"PHP_ENV" is not set');
		}
		return $env;
	}

	/** @inheritdoc */
	public static function getValue($section, $key, $default = null, $allowEmpty = false) {
		$value = parent::getValue($section, $key, $default, $allowEmpty);

		if (!is_string($value) || !String::startsWith($value, '%') || !String::endsWith($value, '%')) {
			return $value;
		}

		$env = substr($value, 1, strlen($value) - 2);
		if (!static::$environments->containsKey($env)) {
			$location = static::$envName . " > " . ($section ? "$section > " : "") . "$key";
			throw new ConfigException("Config file does not contain the environment: \"$env\" requested by $location");
		}

		static::$config = static::$environments->get($env);
		$value = static::getValue($section, $key, $default, $allowEmpty);
		static::$config = static::$environments->get(static::$envName);

		return $value;
	}

	/** @inheritdoc */
	protected static function doSetConfig() {
		parent::doSetConfig();

		/** @var ArrayCollection|null $default */
		if (!$default = static::$config->remove('default')) {
			throw new ConfigException('Config needs to have a default environment');
		}

		static::$envName = static::getEnvironment();

		// Build config for each environment merging default values in
		static::$environments = new ArrayCollection();
		foreach (static::$config as $environment => $envConfig) {
			static::$environments[$environment] = $default->copy()->replaceRecursive($envConfig);
		}

		if (!static::$environments->containsKey(static::$envName)) {
			static::$environments[static::$envName] = $default;
		}

		static::$config = static::$environments[static::$envName];
	}

	protected static $envName;
	/** @var ArrayCollection[]|ArrayCollection */
	protected static $environments;
}
