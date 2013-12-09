<?php
namespace GMO\Common;

/**
 * Class String
 * @package GMO\Common
 * @since 1.2.0
 */
class String {

	public static function contains($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) !== false;
	}

	public static function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	public static function endsWith($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

}