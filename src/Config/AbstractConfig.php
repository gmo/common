<?php
namespace GMO\Common\Config;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Exception\ConfigException;
use GMO\Common\Path;
use GMO\Common\String;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractConfig implements ConfigInterface {

	/** @inheritdoc */
	public static function getList($section, $key, $default = null) {
		if ($default instanceof \Traversable) {
			$default = iterator_to_array($default);
		}
		if (is_array($default)) {
			$default = new ArrayCollection($default);
		}
		return static::getValue($section, $key, $default);
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
		} elseif (!static::$config->containsKey($section)) {
			if ($default === null) {
				throw new ConfigException("Config file section: \"$section\" is missing!");
			} else {
				return $default;
			}
		} else {
			$group = static::$config[$section];
		}

		if (!$group->containsKey($key)) {
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
			static::doSetConfig();
		}
	}

	protected static function doSetConfig() {
		$file = Path::truePath(static::setConfigFile(), static::getProjectDir());
		if (!file_exists($file)) {
			throw new ConfigException("Config file doesn't exist");
		}
		$type = pathinfo($file, PATHINFO_EXTENSION);
		if ($type === "ini") {
			static::$config = parse_ini_file($file, true);
		} elseif ($type === "json") {
			static::$config = json_decode(file_get_contents($file), true);
		} elseif ($type === 'yml') {
			static::$config = Yaml::parse(file_get_contents($file));
		} else {
			throw new ConfigException("Unknown config file format");
		}

		if (!static::$config) {
			throw new ConfigException("Unable to parse $type file: $file");
		}

		static::$config = ArrayCollection::createRecursive(static::$config);
	}

	protected static $configFile;
	/** @var ArrayCollection[]|ArrayCollection */
	protected static $config;
	protected static $projectDir;
}
