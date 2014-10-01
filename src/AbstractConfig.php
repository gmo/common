<?php
namespace GMO\Common;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Exception\ConfigException;

/**
 * Class AbstractConfig
 * @package GMO\Common
 *
 * @since 1.11.0 Added getPath. Deprecated getFile & getDir
 * @since 1.8.0 Changed getValue's $optional param to $default
 *              Added getBool, getFile, getDir methods
 * @since 1.2.0
 */
abstract class AbstractConfig implements IConfig {

	/**
	 * Returns an ArrayCollection from config.
	 * @param string     $section
	 * @param string     $key
	 * @param array|\Traversable|null $default
	 * @return ArrayCollection
	 * @throws ConfigException If key is missing and no default, or the value isn't a list
	 */
	protected static function getList( $section, $key, $default = null ) {
		$value = static::getValue($section, $key, $default);
		if ($value instanceof ArrayCollection) {
			return $value;
		}
		if ($value instanceof \Traversable) {
			$value = iterator_to_array($value);
		} elseif (!is_array($value)) {
			throw new ConfigException('Value in config is not a list');
		}
		return new ArrayCollection($value);
	}

	/**
	 * Returns a boolean value from config.
	 * @param string     $section
	 * @param string     $key
	 * @param mixed|null $default
	 * @return bool
	 * @throws ConfigException If key is missing and no default
	 */
	protected static function getBool( $section, $key, $default = null ) {
		$value = static::getValue($section, $key, $default);
		if (is_string($value)) {
			//                                              booleans from ini are converted to 1|0
			return String::equals($value, "true", false) || String::equals($value, "1");
		} else {
			return (bool)$value;
		}
	}

	/**
	 * Returns a absolute path from config.
	 * Relative paths are converted to absolute based on project root.
	 * @param string     $section
	 * @param string     $key
	 * @param mixed|null $default
	 * @return string
	 * @throws ConfigException If key is missing and no default
	 * @since 1.11.0
	 */
	protected static function getPath( $section, $key, $default = null ) {
		$value = static::getValue($section, $key, $default);
		if (String::equals($value, $default)) {
			return $value;
		}
		return static::toAbsPathFromProjectRoot($value);
	}

	/**
	 * Use {@see AbstractConfig::getPath()} instead
	 * @deprecated
	 */
	protected static function getFile( $section, $key, $default = null ) {
		return static::getPath($section, $key, $default);
	}

	/**
	 * Use {@see AbstractConfig::getPath()} instead
	 * @deprecated
	 */
	protected static function getDir( $section, $key, $default = null ) {
		return static::getPath($section, $key, $default);
	}

	/**
	 * Returns config value. If default isn't specified,
	 * a ConfigException is thrown when the key is missing.
	 * If a default is specified that is returned instead.
	 * @param string     $section
	 * @param string     $key
	 * @param mixed|null $default
	 * @return mixed
	 * @throws ConfigException If key is missing and no default
	 */
	protected static function getValue( $section, $key, $default = null ) {
		static::setConfig();

		if( !isset(self::$config[$section][$key]) ) {
			if ($default === null) {
				throw new ConfigException("Config file key: \"$key\" is missing!");
			} else {
				return $default;
			}
		}

		return self::$config[$section][$key];
	}

	/**
	 * If path is relative, convert it to absolute path based on project root
	 * @param string $path
	 * @return string
	 */
	protected static function toAbsPathFromProjectRoot($path) {
		return Path::truePath($path, static::getProjectDir());
	}

	/**
	 * Returns the absolute path to the project directory
	 * @return string
	 */
	protected static function getProjectDir() {
		if (self::$projectDir === null) {
			$cls = new \ReflectionClass(get_called_class());
			$baseDir = dirname($cls->getFileName());
			self::$projectDir = Path::truePath(static::getProjectRootDir(), $baseDir);
		}
		return self::$projectDir;
	}

	private static function setConfig() {
		if (self::$configFile === null) {
			self::$configFile = static::getConfigFile();
		}
		if (self::$configFile !== static::getConfigFile()) {
			self::$config = null;
		}

		if (self::$config === null) {
			$file = Path::truePath(static::getConfigFile(), static::getProjectDir());
			if (!file_exists($file)) {
				throw new ConfigException("Config file doesn't exist");
			}
			$type = pathinfo($file, PATHINFO_EXTENSION);
			if ($type === "ini") {
				self::$config = parse_ini_file($file, true);
			} elseif ($type === "json") {
				self::$config = json_decode(file_get_contents($file), true);
			} else {
				throw new ConfigException("Unknown config file format");
			}
			if (!self::$config) {
				throw new ConfigException("Unable to parse $type file: $file");
			}
		}
	}

	private static $configFile = null;
	private static $config = null;
	private static $projectDir = null;
}

interface IConfig {

	/**
	 * Absolute directory path to project root or relative this directory
	 * Don't call this method, use getProjectDir() instead.
	 * @return string
	 */
	static function getProjectRootDir();

	/**
	 * Config ini/json absolute file path or relative to project root
	 * @return string
	 */
	static function getConfigFile();
}
