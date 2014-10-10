<?php
namespace GMO\Common;

use GMO\Common\Config\AbstractConfig as NewAbstractConfig;

/**
 * Maintaining backwards compatibility,
 * use {@see \GMO\Common\Config\AbstractConfig} instead.
 * @deprecated
 */
abstract class AbstractConfig extends NewAbstractConfig {

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
	 * If path is relative, convert it to absolute path based on project root
	 * @param string $path
	 * @return string
	 */
	protected static function toAbsPathFromProjectRoot($path) {
		return static::absPath($path);
	}

	abstract public function getConfigFile();
	abstract public function getProjectRootDir();

	public static function setProjectDir() { return static::getProjectRootDir(); }
	public static function setConfigFile() { return static::getConfigFile(); }
}
