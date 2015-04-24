<?php
namespace Gmo\Common\Config;

use Gmo\Common\Collections\ArrayCollection;
use Gmo\Common\Exception\ConfigException;
use Gmo\Common\Path;
use Gmo\Common\Str;
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

	/** @inheritdoc */
	public static function getBool($section, $key, $default = null) {
		static::setConfig();

		if (!static::hasKey($section, $key, $default === null)) {
			return $default;
		}
		$value = static::doGetValue($section, $key);
		if (static::$configFileType === 'ini' && is_string($value)) {
			//                                              booleans from ini are converted to 1|0
			return Str::equals($value, "true", false) || Str::equals($value, "1");
		}
		return (bool)$value;
	}

	/** @inheritdoc */
	public static function getPath($section, $key, $default = null) {
		static::setConfig();

		if (!static::hasKey($section, $key, $default === null)) {
			return $default;
		}

		$value = static::doGetValue($section, $key);
		return static::absPath($value);
	}

	/** @inheritdoc */
	public static function getValue($section, $key, $default = null, $allowEmpty = false) {
		static::setConfig();

		if (!static::hasKey($section, $key, $default === null)) {
			return $default;
		}

		$value = static::doGetValue($section, $key);
		if ($value || is_bool($value)) {
			return $value;
		}
		if ($allowEmpty) {
			if ($default !== null) {
				return $default;
			}
			return $value;
		}
		if ($default === null) {
			throw new ConfigException('Config value for key: "'. ($section ? $section . '.' : '') . $key .'" is missing!');
		}
		return $default;
	}

	/** @inheritdoc */
	public static function overrideValue($section, $key, $value) {
		static::setConfig();

		if ($section !== null && !static::$config->containsKey($section)) {
			static::$config[$section] = new ArrayCollection();
		}
		if ($section === null) {
			static::$config[$key] = $value;
		} else {
			static::$config[$section]->set($key, $value);
		}
	}

	/** @inheritdoc */
	public static function absPath($path) {
		return Path::truePath($path, static::getProjectDir());
	}

	/** @inheritdoc */
	public static function getProjectDir() {
		if (static::$projectDir === null) {
			$cls = new \ReflectionClass(get_called_class());
			$baseDir = dirname($cls->getFileName());
			static::$projectDir = Path::truePath(static::setProjectDir(), $baseDir);
		}
		return static::$projectDir;
	}

	protected static function setConfig() {
		if (static::$configFile !== static::setConfigFile()) {
			static::$config = null;
			static::$configFile = null;
		}
		if (static::$configFile === null) {
			static::$configFile = static::setConfigFile();
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
			static::$configFileType = "ini";
		} elseif ($type === "json") {
			static::$config = json_decode(file_get_contents($file), true);
			static::$configFileType = "json";
		} elseif ($type === 'yml') {
			static::$config = Yaml::parse(file_get_contents($file));
			static::$configFileType = "yaml";
		} else {
			throw new ConfigException("Unknown config file format");
		}

		if (!static::$config) {
			throw new ConfigException("Unable to parse $type file: $file");
		}

		static::$config = ArrayCollection::createRecursive(static::$config);
	}

	/**
	 * NOTE: Does not check if section exists.
	 * @param $section
	 * @param $key
	 * @return null
	 */
	private static function doGetValue($section, $key) {
		if (!$section) {
			return static::$config->get($key);
		}
		return static::$config[$section]->get($key);
	}

	private static function hasKey($section, $key, $throwException = false) {
		if (!$section) {
			if (!static::$config->containsKey($key)) {
				if ($throwException) {
					throw new ConfigException("Config key: \"$key\" is missing!");
				}
				return false;
			}
			return true;
		}
		if (!static::$config->containsKey($section)) {
			if ($throwException) {
				throw new ConfigException("Config section: \"$section\" is missing!");
			}
			return false;
		}
		if (!static::$config[$section] instanceof ArrayCollection) {
			if ($throwException) {
				throw new ConfigException("Config section: \"$section\" is not a collection!");
			}
			return false;
		}
		if (!static::$config[$section]->containsKey($key)) {
			if ($throwException) {
				throw new ConfigException("Config key: \"$key\" is missing!");
			}
			return false;
		}

		return true;
	}

	protected static $configFileType;
	protected static $configFile;
	/** @var ArrayCollection[]|ArrayCollection */
	protected static $config;
	protected static $projectDir;
}
