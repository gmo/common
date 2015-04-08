<?php
namespace Gmo\Common;

use Gmo\Common\Exception\PathException;

/**
 * Class Path
 * @package GMO\Common
 * @since 1.11.0 Added truePath. Deprecated toAbsFile, toAbsDir
 * @since 1.2.0
 */
class Path {

	public static function isAbsolute($path) {
		return substr($path, 0, 1) === "/"
		       && !String::contains($path, './')
		       && !String::contains($path, '..');
	}

	/**
	 * Use {@see Path::truePath()}
	 * @deprecated Path::truePath()
	 */
	public static function toAbsFile($baseDir, $path) {
		return static::truePath($path, $baseDir);
	}

	/**
	 * Use {@see Path::truePath()}
	 * @deprecated Path::truePath()
	 */
	public static function toAbsDir($baseDir, $dir) {
		return static::truePath($dir, $baseDir);
	}

	/**
	 * This function is to replace PHP's extremely buggy realpath().
	 *
	 * Both $path and $basePath can be relative or absolute.
	 * If an absolute path/basePath is provided, then the return path will be absolute.
	 * Vise-versa, if both paths are relative, then the return path will be relative.
	 *
	 * Note: It will remove ending slashes.
	 *
	 * @param string $path            The original path, can be relative etc.
	 * @param string $basePath         Default: current working directory
	 * @throws Exception\PathException If path is trying to move up more directories than the base path has.
	 * @return string The resolved path, it might not exist.
	 * @since 1.11.0
	 */
	public static function truePath($path, $basePath = null) {
		$basePath = $basePath ?: getcwd();
		// attempts to detect if path is relative in which case, add cwd
		$basePath = str_replace(array( '/', '\\' ), DIRECTORY_SEPARATOR, $basePath);
		if (!static::isAbsolute($path)) {
			$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
			$path = $basePath . DIRECTORY_SEPARATOR . $path;
		}
		$abs = String::startsWith($path, '/');
		// resolve path parts (single dot, double dot and double delimiters)
		$path = str_replace(array( '/', '\\' ), DIRECTORY_SEPARATOR, $path);
		$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			if ('.' == $part) { continue; }
			if ('..' == $part) {
				if (empty($absolutes)) {
					throw new PathException('Cannot move up another directory');
				}
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		$path = implode(DIRECTORY_SEPARATOR, $absolutes);
		$path = $abs ? DIRECTORY_SEPARATOR . $path : $path;
		return $path;
	}

	private function __construct() { }
}
