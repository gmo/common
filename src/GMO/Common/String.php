<?php
namespace GMO\Common;

/**
 * Class String
 * @package GMO\Common
 * @since 1.8.0 Added equals method
 *              Added optional caseSensitive params
 * @since 1.6.0 Added splitFirst and splitLast
 *              Renamed case-insensitive functions
 * @since 1.2.0
 */
class String {

	/**
	 * Return whether a term is in a string
	 * @param string $haystack The string to search in
	 * @param string $needle   The search term
	 * @param bool   $caseSensitive Optional. Default: true
	 * @return bool
	 */
	public static function contains($haystack, $needle, $caseSensitive = true) {
		if ($caseSensitive) {
			return $needle === "" || strpos($haystack, $needle) !== false;
		}
		return static::containsInsensitive($haystack, $needle);
	}

	/**
	 * Return whether a term is in a string ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function containsInsensitive($haystack, $needle) {
		return $needle === "" || stripos($haystack, $needle) !== false;
	}

	/**
	 * Compare two strings for identically
	 * @param string $string1
	 * @param string $string2
	 * @param bool   $caseSensitive Optional. Default: true
	 * @return bool
	 */
	public static function equals($string1, $string2, $caseSensitive = true) {
		if ($caseSensitive) {
			return $string1 === $string2;
		}
		return strtolower($string1) === strtolower($string2);
	}

	/**
	 * Return whether a string starts with a term
	 * @param string $haystack The string to search in
	 * @param string $needle   The search term
	 * @param bool   $caseSensitive Optional. Default: true
	 * @return bool
	 */
	public static function startsWith($haystack, $needle, $caseSensitive = true) {
		if ($caseSensitive) {
			return $needle === "" || strpos($haystack, $needle) === 0;
		}
		return static::startsWithInsensitive($haystack, $needle);
	}

	/**
	 * Return whether a string starts with a term ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function startsWithInsensitive($haystack, $needle) {
		return $needle === "" || stripos($haystack, $needle) === 0;
	}

	/**
	 * Return whether a string ends with a term
	 * @param string $haystack The string to search in
	 * @param string $needle   The search term
	 * @param bool   $caseSensitive Optional. Default: true
	 * @return bool
	 */
	public static function endsWith($haystack, $needle, $caseSensitive = true) {
		if ($caseSensitive) {
			return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
		}
		return static::endsWithInsensitive($haystack, $needle);
	}

	/**
	 * Return whether a string ends with a term ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function endsWithInsensitive($haystack, $needle) {
		return $needle === "" || strtolower(substr($haystack, -strlen($needle))) === strtolower($needle);
	}

	/**
	 * Splits a string on the delimiter and returns the first part.
	 * If delimiter is empty false is returned.
	 * If the delimiter is not found in the string the string is returned.
	 * @param string $string The string to split
	 * @param string $delimiter The term to split on
	 * @return string|bool first piece or false
	 */
	public static function splitFirst($string, $delimiter) {
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
	 * @param string $string The string to split
	 * @param string $delimiter The term to split on
	 * @return string|bool last piece or false
	 */
	public static function splitLast($string, $delimiter) {
		if (empty($delimiter)) {
			return false;
		}
		$parts = explode($delimiter, $string);
		return end($parts);
	}

	#region Deprecated functions
	/**
	 * Return whether a term is in a string ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 * @deprecated Use containsInsensitive() instead
	 * @TODO Remove in 2.0
	 */
	public static function iContains($haystack, $needle) {
		return static::containsInsensitive($haystack, $needle);
	}

	/**
	 * Return whether a string starts with a term ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 * @deprecated Use startsWithInsensitive() instead
	 * @TODO Remove in 2.0
	 */
	public static function iStartsWith($haystack, $needle) {
		return static::startsWithInsensitive($haystack, $needle);
	}

	/**
	 * Return whether a string ends with a term ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 * @deprecated Use endsWithInsensitive() instead
	 * @TODO Remove in 2.0
	 */
	public static function iEndsWith($haystack, $needle) {
		return static::endsWithInsensitive($haystack, $needle);
	}
	#endregion
}
