<?php
namespace GMO\Common;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class Http
 * @package GMO\Common
 * @since 1.9.0
 */
class Http {

	/**
	 * A list of HTTP headers to choose the original client IP address from. In
	 * addition to these the RemoteAddr (REMOTE_ADDR) is also used as a final
	 * fallback.
	 */
	public static $HEADERS_TO_CHECK = array(
		'x-forwarded-for',
		'client-ip',
		'x-client-ip',
		'rlnclientipaddr',    // from f5 load balancers
		'proxy-client-ip',
		'wl-proxy-client-ip', // weblogic load balancers
		'x-forwarded',
		'forwarded-for',
		'forwarded',
	);

	/**
	 * Get the most suitable IP address from the given request. This function
	 * checks the headers defined in {@see Http::HEADERS_TO_CHECK}.
	 *
	 * @param Request|null $request Optional request to pull headers from
	 *
	 * @return null|string The found IP address or null if no IP found.
	 */
	public static function getIp($request = null) {
		$request = $request instanceof Request ? $request : Request::createFromGlobals();

		foreach (static::$HEADERS_TO_CHECK as $headerName) {
			if ($request->headers->has($headerName)) {
				$ip = static::extractIp($headerName, $request->headers->get($headerName));
				if ($ip) {
					return $ip;
				}
			}
		}

		return $request->server->get('REMOTE_ADDR');
	}

	/**
	 * Extracts and cleans an IP address from the headerValue. Some headers such
	 * as "X-Forwarded-For" can contain multiple IP addresses such as:
	 * clientIP, proxy1, proxy2...
	 *
	 * This method splits up the headerValue and takes the most appropriate
	 * value as the IP.
	 * @param $headerName
	 * @param $headerValue
	 * @return null|string
	 */
	private static function extractIp($headerName, $headerValue) {
		if ($headerValue === null) {
			return null;
		}
		// "X-Forwarded-For" header can contain many comma separated IPs such as clientIP, proxy1, proxy2...
		// we are interested in the first item
		if ($headerName === 'x-forwarded-for') {
			// loop over all parts and take the first non-empty value
			foreach (explode(',', $headerValue) as $part) {
				$part = trim($part);
				if (static::isPublicIp($part) && !static::isSpecialIp($part)) {
					return $part;
				}
			}
		}

		$headerValue = trim($headerValue);
		if (static::isPublicIp($headerValue) && !static::isSpecialIp($headerValue)) {
			return $headerValue;
		}
		return null;
	}

	private static function isSpecialIp($ip) {
		$parts = explode('.', $ip);
		$ippart = (int) $parts[0];
		return $ippart > 10 && $ippart < 15;
	}

	/**
	 * An IP address is considered public if it is not in any of the following
	 * ranges:
	 *<pre>
	 *  1) Any local address
	 *     IP:  0
	 *
	 *  2) A local loopback address
	 *     range:  127/8
	 *
	 *  3) A site local address i.e. IP is in any of the ranges:
	 *     range:  10/8
	 *     range:  172.16/12
	 *     range:  192.168/16
	 *
	 *  4) A link local address
	 *     range:  169.254/16
	 *</pre>
	 *
	 * @param string $ip The IP address to check
	 * @return bool True if it is a public IP, false if the IP is invalid or is not public.
	 */
	public static function isPublicIp($ip) {
		$ip = ip2long($ip);
		return (0          !== ($ip & 4278190080)) // 0.0.0.0/8
			&& (2130706432 !== ($ip & 4278190080)) // 127.0.0.0/8
			&& (3232235520 !== ($ip & 4294901760)) // 192.168.0.0/16
			&& (2886729728 !== ($ip & 4293918720)) // 172.16.0.0/12
			&& (167772160  !== ($ip & 4278190080)) // 10.0.0.0/8
			&& (2851995648 !== ($ip & 4294901760)) // 169.254.0.0/16
			&& (1681915904 !== ($ip & 4290772992)) // 100.64.0.0/10
			&& (3221225472 !== ($ip & 4294967288)) // 192.0.0.0/29
			&& (3221225984 !== ($ip & 4294967040)) // 192.0.2.0/24
			&& (3227017984 !== ($ip & 4294967040)) // 192.88.99.0/24
			&& (3323068416 !== ($ip & 4294836224)) // 198.18.0.0/15
			&& (3325256704 !== ($ip & 4294967040)) // 198.51.100.0/24
			&& (3405803776 !== ($ip & 4294967040)) // 203.0.113.0/24
			&& (3758096384 !== ($ip & 4026531840)) // 224.0.0.0/4
			&& (4026531840 !== ($ip & 4026531840));// 240.0.0.0/4
	}

	/**
	 * @deprecated
	 */
	public static function getRemoteIP() {
		return static::getIp();
	}
}
