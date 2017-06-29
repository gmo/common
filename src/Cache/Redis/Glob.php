<?php

namespace Gmo\Common\Cache\Redis;

/**
 * @internal
 */
class Glob
{
    /**
     * Filters an array/traversable for items matching a glob.
     *
     * @param string   $glob
     * @param iterable $items
     *
     * @return string[]
     */
    public static function filter(string $glob, iterable $items): array
    {
        $items = $items instanceof \Traversable ? iterator_to_array($items) : $items;

        // shortcut to match everything
        if ($glob === '*') {
            return $items;
        }

        // shortcut for static glob
        if (!self::isDynamic($glob)) {
            $items = array_flip($items);
            if (isset($items[$glob])) {
                return [$glob];
            }

            return [];
        }

        $regExp = self::toRegEx($glob);

        // Compute static prefix to save on regex calls items not mathing prefix
        $staticPrefix = self::getStaticPrefix($glob);
        if (!empty($staticPrefix)) {
            return array_filter($items, function ($item) use ($staticPrefix, $regExp) {
                return strpos($item, $staticPrefix) === 0 && preg_match($regExp, $item);
            });
        }

        return array_filter($items, function ($item) use ($regExp) {
            return preg_match($regExp, $item);
        });
    }

    /**
     * Returns whether the glob is dynamic.
     *
     * A glob is dynamic if it contains *, ?, or [.
     *
     * @param string $glob
     *
     * @return bool
     */
    public static function isDynamic(string $glob): bool
    {
        return strpos($glob, '*') !== false || strpos($glob, '?') !== false || strpos($glob, '[') !== false;
    }

    /**
     * Converts the glob to a regular expression.
     *
     * @param string $glob
     *
     * @return string
     */
    public static function toRegEx(string $glob): string
    {
        $quoted = str_replace(['?', '*'], ['.', '.*'], $glob);
        $regExp = '~^' . $quoted . '$~';

        return $regExp;
    }

    /**
     * Returns the static prefix of a glob.
     *
     * If the glob does not contain wildcards, the full glob is returned.
     *
     * @param string $glob
     *
     * @return string
     */
    public static function getStaticPrefix(string $glob): string
    {
        if (preg_match('#^.*((?<!\\\\)\[|(?<!\\\\)\*|(?<!\\\\)\?).*$#', $glob, $matches, PREG_OFFSET_CAPTURE)) {
            $prefix = substr($glob, 0, $matches[1][1]);
            // Prefix will be used for string matching not regex, so we need to
            // unescape characters that were escaped by user for this glob.
            return str_replace(['\[', '\]', '\?', '\*', '\-', '\^'], ['[', ']', '?', '*', '-', '^'], $prefix);
        }

        return $glob;
    }

    private function __construct()
    {
    }
}
