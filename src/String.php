<?php

namespace GMO\Common;

Deprecated::cls('GMO\Common\String', 1.0, 'GMO\Common\Str');

/**
 * @deprecated Use {@see Str} instead.
 *
 * @since      1.15.0 Added remove* and className methods
 * @since      1.8.0 Added equals method
 *              Added optional caseSensitive params
 * @since      1.6.0 Added splitFirst and splitLast
 *              Renamed case-insensitive functions
 * @since      1.2.0
 */
class String extends Str
{
    /**
     * Return whether a term is in a string ignoring case
     *
     * @param string $haystack The string to search in
     * @param string $needle   The search term
     *
     * @return bool
     * @deprecated Use containsInsensitive() instead
     * @TODO       Remove in 2.0
     */
    public static function iContains($haystack, $needle)
    {
        Deprecated::method(1.0);

        return static::containsInsensitive($haystack, $needle);
    }

    /**
     * Return whether a string starts with a term ignoring case
     *
     * @param string $haystack The string to search in
     * @param string $needle   The search term
     *
     * @return bool
     * @deprecated Use startsWithInsensitive() instead
     * @TODO       Remove in 2.0
     */
    public static function iStartsWith($haystack, $needle)
    {
        Deprecated::method(1.0);

        return static::startsWithInsensitive($haystack, $needle);
    }

    /**
     * Return whether a string ends with a term ignoring case
     *
     * @param string $haystack The string to search in
     * @param string $needle   The search term
     *
     * @return bool
     * @deprecated Use endsWithInsensitive() instead
     * @TODO       Remove in 2.0
     */
    public static function iEndsWith($haystack, $needle)
    {
        Deprecated::method(1.0);

        return static::endsWithInsensitive($haystack, $needle);
    }
}
