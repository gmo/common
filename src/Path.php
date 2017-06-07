<?php

namespace GMO\Common;

use GMO\Common\Exception\PathException;

/**
 * Class Path
 *
 * @package    GMO\Common
 * @since      1.11.0 Added truePath. Deprecated toAbsFile, toAbsDir
 * @since      1.2.0
 * @deprecated since 1.30 will be removed in 2.0. Use {@see \Webmozart\PathUtil\Path} instead.
 */
class Path
{
    /**
     * @param string $path
     *
     * @return bool
     *
     * @deprecated since 1.30 will be removed in 2.0. Use {@see \Webmozart\PathUtil\Path::isAbsolute} instead.
     */
    public static function isAbsolute($path)
    {
        Deprecated::method(1.30, 'Webmozart\PathUtil\Path::isAbsolute');

        return substr($path, 0, 1) === "/"
            && !Str::contains($path, './')
            && !Str::contains($path, '..');
    }

    /**
     * @deprecated since 1.30 will be removed in 2.0. Use {@see \Webmozart\PathUtil\Path::makeAbsolute} instead.
     */
    public static function toAbsFile($baseDir, $path)
    {
        Deprecated::method(1.30, 'Webmozart\PathUtil\Path::makeAbsolute');

        return static::truePath($path, $baseDir);
    }

    /**
     * @deprecated since 1.30 will be removed in 2.0. Use {@see \Webmozart\PathUtil\Path::makeAbsolute} instead.
     */
    public static function toAbsDir($baseDir, $dir)
    {
        Deprecated::method(1.30, 'Webmozart\PathUtil\Path::makeAbsolute');

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
     * @param string $path     The original path, can be relative etc.
     * @param string $basePath Default: current working directory
     *
     * @throws Exception\PathException If path is trying to move up more directories than the base path has.
     * @return string The resolved path, it might not exist.
     * @since      1.11.0
     * @deprecated since 1.30 will be removed in 2.0. Use {@see \Webmozart\PathUtil\Path::makeAbsolute} instead.
     */
    public static function truePath($path, $basePath = null)
    {
        Deprecated::method(1.30, 'Webmozart\PathUtil\Path::makeAbsolute');

        $basePath = $basePath ?: getcwd();
        // attempts to detect if path is relative in which case, add cwd
        $basePath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $basePath);
        if (!static::isAbsolute($path)) {
            $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
            $path = $basePath . DIRECTORY_SEPARATOR . $path;
        }
        $abs = Str::startsWith($path, '/');
        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
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

    private function __construct()
    {
    }
}
