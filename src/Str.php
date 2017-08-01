<?php

namespace Gmo\Common;

use Bolt\Collection\Bag;

/**
 * Class Str
 *
 * @since   1.15.0 Added remove* and className methods
 * @since   1.8.0 Added equals method
 *              Added optional caseSensitive params
 * @since   1.6.0 Added splitFirst and splitLast
 *              Renamed case-insensitive functions
 * @since   1.2.0
 */
class Str extends \Bolt\Common\Str
{
    /**
     * Return whether a term is in a string
     *
     * @param string $haystack      The string to search in
     * @param string $needle        The search term
     * @param bool   $caseSensitive Optional. Default: true
     *
     * @return bool
     */
    public static function contains(string $haystack, string $needle, bool $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $needle === '' || strpos($haystack, $needle) !== false;
        }

        return $needle === '' || stripos($haystack, $needle) !== false;
    }

    /**
     * Compare two strings for identically
     *
     * @param string $string1
     * @param string $string2
     * @param bool   $caseSensitive Optional. Default: true
     *
     * @return bool
     */
    public static function equals(string $string1, string $string2, bool $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $string1 === $string2;
        }

        return strtolower($string1) === strtolower($string2);
    }

    /**
     * Return whether a string starts with a term
     *
     * @param string $haystack      The string to search in
     * @param string $needle        The search term
     * @param bool   $caseSensitive Optional. Default: true
     *
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle, bool $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $needle === '' || strpos($haystack, $needle) === 0;
        }

        return $needle === '' || stripos($haystack, $needle) === 0;
    }

    /**
     * Splits a string on the delimiter.
     *
     * @param string $subject   The string to split
     * @param string $delimiter The term to split on
     * @param int    $limit     If limit is set and positive, the returned array will contain a maximum of limit
     *                          elements with the last element containing the rest of string.
     *
     *                          If the limit parameter is negative, all components except the last -limit are returned.
     *
     *                          If the limit parameter is zero, then this is treated as 1.
     *
     * @return Bag A bag containing the string parts.
     *         The bag will be empty if the delimiter is an empty string or
     *         if delimiter contains a value that is not contained in string
     *         and a negative limit is used.
     */
    public static function explode(string $subject, string $delimiter, int $limit = null): Bag
    {
        if (empty($delimiter)) {
            return new Bag();
        }
        $parts = $limit === null ? explode($delimiter, $subject) : explode($delimiter, $subject, $limit);

        return new Bag($parts ?: []);
    }
}
