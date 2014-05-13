<?php
namespace GMO\Common;

/**
 * Class Http
 * @package GMO\Common
 * @since 1.9.0
 */
class Http {

	public static function getRemoteIP() {
		// Check to see if an HTTP_X_FORWARDED_FOR header is present.
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			// If the header is present, use the last IP address.
			$temp_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return $temp_array[count($temp_array) - 1];
		}

		if (isset($_SERVER["REMOTE_ADDR"])) {
			// If the header is not present, use the
			// default server variable for remote address.
			return $_SERVER['REMOTE_ADDR'];
		}

		return "";
	}
}
