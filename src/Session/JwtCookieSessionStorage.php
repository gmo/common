<?php
namespace Gmo\Common\Session;

use Gmo\Common\Collections\Arr;
use JWT;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * @deprecated since 1.30 will be removed in 2.0.
 */
class JwtCookieSessionStorage implements SessionStorageInterface {

	/**
	 * Constructor.
	 * @param string                  $cookieName
	 * @param string                  $secret Secret for encrypting/decrypting the JWT packet in the cookie
	 * @param string|null             $cookieDomain
	 * @param ParameterBag|array|null $cookies
	 */
	public function __construct($cookieName, $secret, $cookieDomain = null, $cookies = null) {
		$this->cookieName = $cookieName;
		$this->secret = $secret;
		$this->cookieDomain = $cookieDomain;

		$this->values = $this->parseCookieData($cookieName, $cookies);

		$this->metadataBag = new MetadataBag();
	}

	/**
	 * @param $cookieName
	 * @param $cookies
	 * @return array
	 */
	protected function parseCookieData($cookieName, $cookies) {
		if ($cookies instanceof ParameterBag) {
			$cookieData = $cookies->get($cookieName, '');
		} else {
			$cookies = is_array($cookies) ? $cookies : $_COOKIE;
			$cookieData = Arr::get($cookies, $cookieName, '');
		}

		try {
			return Arr::objectToArray(JWT::decode($cookieData, $this->secret));
		} catch (\Exception $e) {
			return array();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function start() {
		if ($this->started) {
			return true;
		}

		if (headers_sent($file, $line)) {
			throw new \RuntimeException(sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line));
		}

		$this->connectValuesToBags();
		$this->started = true;
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted() {
		return $this->started;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId() {
		return $this->cookieName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setId($id) {
		$this->cookieName = $id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return $this->cookieName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name) {
		$this->cookieName = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function regenerate($destroy = false, $lifetime = null) {
		if (null !== $lifetime) {
			ini_set('session.cookie_lifetime', $lifetime);
		}

		if ($destroy) {
			$this->metadataBag->stampNew();
			$this->setCookie('');
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		$this->setCookie(JWT::encode($this->values, $this->secret));
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear() {
		foreach ($this->bags as $bag) {
			$bag->clear();
		}

		$this->values = array();
		$this->connectValuesToBags();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBag($name) {
		if (!isset($this->bags[$name])) {
			throw new \InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
		}

		if (!$this->started) {
			$this->start();
		}

		return $this->bags[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerBag(SessionBagInterface $bag) {
		$this->bags[$bag->getName()] = $bag;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadataBag() {
		return $this->metadataBag;
	}

	/**
	 * Sets the JWT cookie.
	 * @param string $cookieData
	 */
	protected function setCookie($cookieData) {
		if (!headers_sent()) {
			setcookie($this->cookieName, $cookieData, $this->metadataBag->getLifetime(), '/', $this->cookieDomain);
		}
	}

	/**
	 * Connects $this->values to the data bags via pass-by-reference
	 */
	protected function connectValuesToBags() {
		/** @var SessionBagInterface[] $bags */
		$bags = array_merge($this->bags, array($this->metadataBag));

		foreach ($bags as $bag) {
			$key = $bag->getStorageKey();
			$this->values[$key] = isset($this->values[$key]) ? $this->values[$key] : array();
			$bag->initialize($this->values[$key]);
		}
	}

	protected $cookieName;
	protected $secret;
	protected $cookieDomain;
	protected $values;

	/**
	 * Array of SessionBagInterface.
	 *
	 * @var SessionBagInterface[]
	 */
	protected $bags;

	/** @var bool */
	protected $started = false;
	/** @var MetadataBag */
	protected $metadataBag;
}
