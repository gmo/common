<?php
namespace GMO\Common\Web;

use Carbon\Carbon;
use GMO\Common\Web\Exception;
use LogicException;
use Symfony\Component\HttpFoundation\ParameterBag as ParameterBagBase;

/**
 * {@inheritdoc}
 *
 * Additional methods have been added.
 */
class ParameterBag extends ParameterBagBase {

	/**
	 * Returns a required parameter by name.
	 *
	 * @param string $key
	 *
	 * @throws Exception\MissingParameterException If the parameter does not exist.
	 * @throws Exception\InvalidParameterException If the parameter is empty.
	 *
	 * @return mixed
	 */
	public function getRequired($key) {
		if (!$this->has($key)) {
			throw new Exception\MissingParameterException($key);
		}

		$value = $this->get($key);

		if (empty($value)) {
			throw new Exception\InvalidParameterException($key, '%s should not be empty');
		}

		return $value;
	}

	/**
	 * Wrapper to throw NoKeyException if API key does not exist.
	 *
	 * @param string $keyName
	 *
	 * @throws Exception\NoKeyException
	 *
	 * @return mixed
	 */
	public function getApiKey($keyName = 'key') {
		try {
			return $this->getRequired($keyName);
		} catch (Exception\MissingParameterException $e) {
			throw new Exception\NoKeyException($keyName);
		}
	}

	/**
	 * Returns a parameter value converted to a Carbon instance.
	 *
	 * If the default value is an int or string it will be converted to a Carbon instance.
	 *
	 * @param string                 $key     The key
	 * @param Carbon|int|string|null $default The default value if the parameter key doesn't exist or is empty
	 * @param string                 $tz      The timezone to create the Carbon instances with. Default: UTC
	 *
	 * @throws Exception\InvalidParameterException If the parameter value fails to parse
	 * @throws LogicException If Carbon is not installed
	 *
	 * @return Carbon|null A Carbon timestamp or null if the key was empty and default is null
	 */
	public function getTimestamp($key, $default = null, $tz = 'UTC') {
		if (!class_exists('\Carbon\Carbon')) {
			throw new LogicException('Carbon library is not installed');
		}

		$timestamp = $this->get($key);
		if (!empty($timestamp)) {
			try {
				if (is_numeric($timestamp)) {
					$carbon = Carbon::createFromTimestamp($timestamp, $tz);
				} else {
					$carbon = new Carbon($timestamp, $tz);
				}
			} catch (\Exception $e) {
				throw new Exception\InvalidParameterException($key);
			}
		} else {
			if ($default === null) {
				return null;
			}
			if (!$default instanceof Carbon) {
				$carbon = Carbon::createFromTimestamp($default, $tz);
			} else {
				$carbon = $default;
			}
		}

		// Assert date is in valid range
		$carbon = max($carbon, Carbon::create(1000, 1, 1, 0, 0, 0));
		$carbon = min($carbon, Carbon::create(9999, 12, 31, 23, 59, 59));

		return $carbon;
	}
}
