<?php
namespace GMO\Common;

/**
 * Class Path
 * @package GMO\Common
 * @since 1.2.0
 */
class Path {

	public static function isAbsolute($path) {
		return substr($path, 0, 1) === "/"
		       && !String::contains($path, './')
		       && !String::contains($path, '..');
	}

	public static function toAbsFile($baseDir, $path) {
		if (static::isAbsolute($path)) {
			return $path;
		}
		$dir = dirname($path);
		$file = basename($path);
		$absFile = realpath($baseDir . "/" . $dir) . "/" . basename($file);
		return $absFile;
	}

	public static function toAbsDir($baseDir, $dir) {
		if (static::isAbsolute($dir)) {
			return $dir;
		}
		$absPath = realpath($baseDir . "/" . $dir);
		return $absPath;
	}

} 