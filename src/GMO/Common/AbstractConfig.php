<?php
namespace GMO\Common;

use GMO\Common\Exception\ConfigException;

/**
 * Class AbstractConfig
 * @package GMO\Common
 * @since 1.2.0
 */
abstract class AbstractConfig implements IConfig {

	/**
	 * Returns config value.
	 * @param string $section
	 * @param string $key
	 * @param bool   $optional Return false or throw ConfigException
	 * @return mixed
	 * @throws ConfigException If key is missing and not optional
	 */
	protected static function getValue( $section, $key, $optional = false ) {
		static::setConfig();

		if( !isset(self::$config[$section][$key]) ) {
			if ($optional) {
				return false;
			} else {
				throw new ConfigException("Config file key: \"$key\" is missing!");
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
		return Path::toAbsFile(static::getProjectDir(), $path);
	}

	/**
	 * Returns the absolute path to the project directory
	 * @return string
	 */
	protected static function getProjectDir() {
		if (self::$projectDir === null) {
			$cls = new \ReflectionClass(get_called_class());
			$baseDir = dirname($cls->getFileName());
			self::$projectDir = Path::toAbsDir($baseDir, static::getProjectRootDir());
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
			$file = Path::toAbsFile(static::getProjectDir(), static::getConfigFile());
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