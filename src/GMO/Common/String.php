<?php
namespace GMO\Common;

/**
 * Class String
 * @package GMO\Common
 * @since 1.2.0
 */
class String {

	/**
	 * Return whether a term is in a string
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function contains($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) !== false;
	}

	/**
	 * Return whether a term is in a string ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function iContains($haystack, $needle) {
		return $needle === "" || stripos($haystack, $needle) !== false;
	}

	/**
	 * Return whether a string starts with a term
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	/**
	 * Return whether a string starts with a term ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function iStartsWith($haystack, $needle) {
		return $needle === "" || stripos($haystack, $needle) === 0;
	}

	/**
	 * Return whether a string ends with a term
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function endsWith($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

	/**
	 * Return whether a string ends with a term ignoring case
	 * @param string $haystack The string to search in
	 * @param string $needle The search term
	 * @return bool
	 */
	public static function iEndsWith($haystack, $needle) {
		return $needle === "" || strtolower(substr($haystack, -strlen($needle))) === strtolower($needle);
	}

}