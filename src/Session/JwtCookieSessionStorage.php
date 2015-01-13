<?php
namespace GMO\Common\Session;

use GMO\Common\Collection;
use JWT;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

class JwtCookieSessionStorage implements SessionStorageInterface {
	/**
	 * Constructor.
	 * @param string $cookieName
	 * @param string $secret Secret for encrypting/decrypting the JWT packet in the cookie
	 * @param string|null $cookieDomain
	 */
	public function __construct($cookieName, $secret, $cookieDomain = null) {
		$this->cookieName = $cookieName;
		$this->secret = $secret;
		$this->cookieDomain = $cookieDomain;
		$this->rawCookie = Collection::get($_COOKIE, $cookieName, '');

		try {
			$this->values = $this->objectToArray( JWT::decode($this->rawCookie, $this->secret) );
		} catch(\Exception $e) {
			$this->values = array();
		}

		$this->metadataBag = new MetadataBag();
	}

	/**
	 * {@inheritdoc}
	 */
	public function start() {
		$this->started = true;
		$this->connectValuesToBags();
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
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */

	public function regenerate($destroy = false, $lifetime = null) {
            if ($destroy) {
			$this->metadataBag->stampNew();
		}

		$this->cookieLifetime = $lifetime;
		$this->setCookie();
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		$this->rawCookie = JWT::encode($this->values, $this->secret);
		$this->setCookie();
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
	 */
	protected function setCookie() {
		// Supresses warnings in case it's called after HTTP headers are sent.
		@setcookie($this->cookieName, $this->rawCookie, $this->cookieLifetime, "/", $this->cookieDomain);
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

	/**
	 * Recursively converts stdClass to array
	 * @param $object
	 * @return array
	 */
	protected function objectToArray($object) {
		$array = (array) $object;
		foreach($array as $key => $val) {
			if(!is_object($val)) {
				continue;
			}

			$array[$key] = $this->objectToArray($val);
		}

		return $array;
	}

	protected $cookieName;
	protected $secret;
	protected $cookieDomain;
	protected $rawCookie;
	protected $values;
	protected $cookieLifetime = null;

	/**
	 * Array of SessionBagInterface.
	 *
	 * @var SessionBagInterface[]
	 */
	protected $bags;

	/** @var bool */
	protected $started = false;
	protected $name;
	/** @var MetadataBag */
	protected $metadataBag;
}