<?php
namespace GMO\Common\Session;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * @deprecated since 1.30 will be removed in 2.0.
 */
class AutoSavingAttributeBag extends AttributeBag {

	/**
	 * Constructor.
	 *
	 * @param SessionStorageInterface $sessionStorage
	 * @param string $storageKey The key used to store attributes in the session
	 */
	public function __construct(SessionStorageInterface $sessionStorage, $storageKey = '_sf2_attributes') {
		parent::__construct($storageKey);
		$this->sessionStorage = $sessionStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($name, $value) {
		parent::set($name, $value);
		$this->sessionStorage->save();
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace(array $attributes) {
		parent::replace($attributes);
		$this->sessionStorage->save();
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($name) {
		$retval = parent::remove($name);
		$this->sessionStorage->save();
		return $retval;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear() {
		$retval = parent::clear();
		$this->sessionStorage->save();
		return $retval;
	}

	/** @var SessionStorageInterface */
	protected $sessionStorage;
}
