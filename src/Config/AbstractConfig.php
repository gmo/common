<?php
namespace GMO\Common\Config;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Exception\ConfigException;
use GMO\Common\Path;
use GMO\Common\String;

abstract class AbstractConfig implements ConfigInterface {

	public static function getList($section, $key, $default = null) {
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

	public static function getBool($section, $key, $default = null) {
		$value = static::getValue($section, $key, $default);
		if (is_string($value)) {
			//                                              booleans from ini are converted to 1|0
			return String::equals($value, "true", false) || String::equals($value, "1");
		} else {
			return (bool)$value;
		}
	}

	public static function getPath($section, $key, $default = null) {
		$value = static::getValue($section, $key, $default);
		if (String::equals($value, $default)) {
			return $value;
		}
		return static::absPath($value);
	}

	public static function getValue($section, $key, $default = null) {
		static::setConfig();

		if ($section === null) {
			$group = static::$config;
		} elseif (!isset(static::$config[$section])) {
			if ($default === null) {
				throw new ConfigException("Config file section: \"$section\" is missing!");
			} else {
				return $default;
			}
		} else {
			$group = static::$config[$section];
		}

		if (!isset($group[$key])) {
			if ($default === null) {
				throw new ConfigException("Config file key: \"$key\" is missing!");
			} else {
				return $default;
			}
		}

		return $group[$key];
	}

	public static function overrideValue($section, $key, $value) {
		static::setConfig();

		if ($section !== null && !isset(static::$config[$section])) {
			static::$config[$section] = array();
		}
		if ($section === null) {
			static::$config[$key] = $value;
		} else {
			static::$config[$section][$key] = $value;
		}
	}

	public static function absPath($path) {
		return Path::truePath($path, static::getProjectDir());
	}

	/**
	 * Returns the absolute path to the project directory
	 * @return string
	 */
	public static function getProjectDir() {
		if (static::$projectDir === null) {
			$cls = new \ReflectionClass(get_called_class());
			$baseDir = dirname($cls->getFileName());
			static::$projectDir = Path::truePath(static::setProjectDir(), $baseDir);
		}
		return static::$projectDir;
	}

	protected static function setConfig() {
		if (static::$configFile === null) {
			static::$configFile = static::setConfigFile();
		}
		if (static::$configFile !== static::setConfigFile()) {
			static::$config = null;
		}

		if (static::$config === null) {
			$file = Path::truePath(static::setConfigFile(), static::getProjectDir());
			if (!file_exists($file)) {
				throw new ConfigException("Config file doesn't exist");
			}
			$type = pathinfo($file, PATHINFO_EXTENSION);
			if ($type === "ini") {
				static::$config = parse_ini_file($file, true);
			} elseif ($type === "json") {
				static::$config = json_decode(file_get_contents($file), true);
			} else {
				throw new ConfigException("Unknown config file format");
			}
			if (!static::$config) {
				throw new ConfigException("Unable to parse $type file: $file");
			}
		}
	}

	protected static $configFile;
	protected static $config;
	protected static $projectDir;
}
