<?php
namespace GMO\TestHelper;

use GMO\Common\Deprecated;

Deprecated::cls('\GMO\TestHelper\BehaviorDrivenTestCase', 1.0);

/**
 * @deprecated will be removed in 2.0.
 */
abstract class BehaviorDrivenTestCase extends \PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass()
	{
		static::given();
		static::when();
	}
	public static function tearDownAfterClass()
	{
		static::cleanUp();
	}

	/**
	 * Need to override with the given context
	 */
	protected static function given() {}
	/**
	 * Need to override and call the method or generate the event that is being tested
	 */
	protected static function when() {}
	/**
	 * Need to override and clean up any items like clearing queues or truncating tables
	 */
	protected static function cleanUp() {}
} 
