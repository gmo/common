<?php
namespace GMO\Common\Config;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Exception\ConfigException;

interface ConfigInterface {

	/**
	 * Returns an ArrayCollection from config.
	 * @param string|null             $section Section name, null for no section
	 * @param string                  $key
	 * @param array|\Traversable|null $default
	 * @return ArrayCollection
	 * @throws ConfigException If value is missing and no default
	 * @deprecated
	 * Use {@see ConfigInterface::getValue getValue} instead
	 */
	public static function getList($section, $key, $default = null);

	/**
	 * Returns a boolean value from config.
	 * @param string|null $section Section name, null for no section
	 * @param string      $key
	 * @param mixed|null  $default
	 * @return bool
	 * @throws ConfigException If value is missing and no default
	 */
	public static function getBool($section, $key, $default = null);

	/**
	 * Returns a absolute path from config.
	 * Relative paths are converted to absolute based on project root.
	 * @param string|null $section Section name, null for no section
	 * @param string      $key
	 * @param mixed|null  $default
	 * @return string
	 * @throws ConfigException If value is missing and no default
	 * @since 1.11.0
	 */
	public static function getPath($section, $key, $default = null);

	/**
	 * Returns config value. If default isn't specified,
	 * a ConfigException is thrown when the value is missing.
	 * If a default is specified that is returned instead.
	 * @param string|null $section Section name, null for no section
	 * @param string      $key
	 * @param mixed|null  $default
	 * @return mixed
	 * @throws ConfigException If value is missing and no default
	 */
	public static function getValue($section, $key, $default = null);

	/**
	 * Override a config value. Useful for unit testing.
	 * @param string|null $section Section name, null for no section
	 * @param string      $key
	 * @param mixed       $value
	 */
	public static function overrideValue($section, $key, $value);

	/**
	 * If path is relative, convert it to absolute path based on project root
	 * @param string $path
	 * @return string
	 */
	public static function absPath($path);

	/**
	 * Returns the absolute path to the project directory
	 * @return string
	 */
	public static function getProjectDir();

	/**
	 * Absolute directory path to project root or relative this directory
	 * @return string
	 */
	public static function setProjectDir();

	/**
	 * Config ini/json absolute file path or relative to project root
	 * @return string
	 */
	public static function setConfigFile();
}
