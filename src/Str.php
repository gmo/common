<?php

namespace Gmo\Common;

use Bolt\Collection\Bag;

/**
 * Class Str
 *
 * @package GMO\Common
 * @since   1.15.0 Added remove* and className methods
 * @since   1.8.0 Added equals method
 *              Added optional caseSensitive params
 * @since   1.6.0 Added splitFirst and splitLast
 *              Renamed case-insensitive functions
 * @since   1.2.0
 */
class Str
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
    public static function contains($haystack, $needle, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $needle === "" || strpos($haystack, $needle) !== false;
        }

        return static::containsInsensitive($haystack, $needle);
    }

    /**
     * Return whether a term is in a string ignoring case
     *
     * @param string $haystack The string to search in
     * @param string $needle   The search term
     *
     * @return bool
     */
    public static function containsInsensitive($haystack, $needle)
    {
        return $needle === "" || stripos($haystack, $needle) !== false;
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
    public static function equals($string1, $string2, $caseSensitive = true)
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
    public static function startsWith($haystack, $needle, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $needle === "" || strpos($haystack, $needle) === 0;
        }

        return static::startsWithInsensitive($haystack, $needle);
    }

    /**
     * Return whether a string starts with a term ignoring case
     *
     * @param string $haystack The string to search in
     * @param string $needle   The search term
     *
     * @return bool
     */
    public static function startsWithInsensitive($haystack, $needle)
    {
        return $needle === "" || stripos($haystack, $needle) === 0;
    }

    /**
     * Return whether a string ends with a term
     *
     * @param string $haystack      The string to search in
     * @param string $needle        The search term
     * @param bool   $caseSensitive Optional. Default: true
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
        }

        return static::endsWithInsensitive($haystack, $needle);
    }

    /**
     * Return whether a string ends with a term ignoring case
     *
     * @param string $haystack The string to search in
     * @param string $needle   The search term
     *
     * @return bool
     */
    public static function endsWithInsensitive($haystack, $needle)
    {
        return $needle === "" || strtolower(substr($haystack, -strlen($needle))) === strtolower($needle);
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
    public static function explode($subject, $delimiter, $limit = null)
    {
        if (empty($delimiter)) {
            return new Bag();
        }
        $parts = $limit === null ? explode($delimiter, $subject) : explode($delimiter, $subject, $limit);

        return new Bag($parts ?: array());
    }

    /**
     * Splits a string on the delimiter and returns the first part.
     * If delimiter is empty false is returned.
     * If the delimiter is not found in the string the string is returned.
     *
     * @param string $string    The string to split
     * @param string $delimiter The term to split on
     *
     * @return string|bool first piece or false
     */
    public static function splitFirst($string, $delimiter)
    {
        if (empty($delimiter)) {
            return false;
        }
        $parts = explode($delimiter, $string);

        return reset($parts);
    }

    /**
     * Splits a string on the delimiter and returns the last part.
     * If delimiter is empty false is returned.
     * If the delimiter is not found in the string the string is returned.
     *
     * @param string $string    The string to split
     * @param string $delimiter The term to split on
     *
     * @return string|bool last piece or false
     */
    public static function splitLast($string, $delimiter)
    {
        if (empty($delimiter)) {
            return false;
        }
        $parts = explode($delimiter, $string);

        return end($parts);
    }


    /**
     * Removes the first occurrence of the value from the string.
     *
     * The full string is returned if the value does not exist in the string.
     *
     * @param string $string        The string to search in
     * @param string $value         The value to search for
     * @param bool   $caseSensitive Should the search be case sensitive
     *
     * @return string
     */
    public static function removeFirst($string, $value, $caseSensitive = true)
    {
        return static::replaceFirst($string, $value, '', $caseSensitive);
    }

    /**
     * Removes the last occurrence of the value from the string.
     *
     * The full string is returned if the value does not exist in the string.
     *
     * @param string $string        The string to search in
     * @param string $value         The value to search for
     * @param bool   $caseSensitive Should the search be case sensitive
     *
     * @return string
     */
    public static function removeLast($string, $value, $caseSensitive = true)
    {
        return static::replaceLast($string, $value, '', $caseSensitive);
    }

    /**
     * Replaces the first occurrence of $from with $to in the given $subject.
     *
     * If $from is not found in the $subject, the $subject is returned.
     *
     * @param string $subject       The string to search in
     * @param string $from          What to search the subject for
     * @param string $to            What to replace $from with
     * @param bool   $caseSensitive Whether finding $from in the subject is case sensitive
     *
     * @return string
     */
    public static function replaceFirst($subject, $from, $to, $caseSensitive = true)
    {
        $pos = $caseSensitive ? strpos($subject, $from) : stripos($subject, $from);
        if ($pos === false) {
            return $subject;
        }

        return substr_replace($subject, $to, $pos, strlen($from));
    }

    /**
     * Replaces the last occurrence of $from with $to in the given $subject.
     *
     * If $from is not found in the $subject, the $subject is returned.
     *
     * @param string $subject       The string to search in
     * @param string $from          What to search the subject for
     * @param string $to            What to replace $from with
     * @param bool   $caseSensitive Whether finding $from in the subject is case sensitive
     *
     * @return string
     */
    public static function replaceLast($subject, $from, $to, $caseSensitive = true)
    {
        $pos = $caseSensitive ? strrpos($subject, $from) : strripos($subject, $from);
        if ($pos === false) {
            return $subject;
        }

        return substr_replace($subject, $to, $pos, strlen($from));
    }

    /**
     * Returns the class name without the namespace.
     *
     * If the class does not exist false is returned.
     *
     * @param string|object $cls object or fully qualified class name
     *
     * @return string|false
     */
    public static function className($cls)
    {
        if (is_string($cls) && !class_exists($cls)) {
            return false;
        } elseif (is_object($cls)) {
            $cls = get_class($cls);
        }

        return Str::splitLast($cls, "\\");
    }

    /**
     * Makes a technical name human readable.
     *
     * Sequences of underscores or camel cased are replaced by single spaces.
     * The first letter of the resulting string is capitalized,
     * while all other letters are turned to lowercase.
     *
     * @author Symfony
     *
     * @param string $text The text to humanize.
     *
     * @return string The humanized text.
     */
    public static function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', ' '), $text))));
    }

    private function __construct()
    {
    }
}
