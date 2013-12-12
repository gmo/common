<?php
namespace UnitTest;

use GMO\Common\AbstractSerializable;
use GMO\TestHelper\BehaviorDrivenTestCase;

require_once __DIR__ . "/../tester_autoload.php";

# region given_a_fully_hydrated_object

class given_a_fully_hydrated_object extends BehaviorDrivenTestCase {

	protected static function given() {
		$address = new Address("123 Testing Way", "Unit Testing Ville", "12345");
		self::$sut = new Contact("John", "J", "Doe", $address, 21, new \DateTime('2000-01-01 12:00:00'));
	}
	protected static function when() {
	}

	public function test_toArray_contains_all_the_properties_and_values() {

		$result = self::$sut->toArray();

		$this->assertEquals("John", $result["firstName"]);
		$this->assertEquals("J", $result["middleName"]);
		$this->assertEquals("Doe", $result["lastName"]);
		$this->assertEquals(21, $result["age"]);
		/** @var \DateTime $timestamp */
		$timestamp = $result["timestamp"];
		$this->assertEquals("2000-01-01 12:00:00", $timestamp->format("Y-m-d h:i:s"));

		$address = $result["address"];
		$this->assertEquals("123 Testing Way", $address["street"]);
		$this->assertEquals("Unit Testing Ville", $address["city"]);
		$this->assertEquals("12345", $address["zip"]);
	}

	public function test_toJson() {

		$this->assertEquals('{"firstName":"John","middleName":"J","lastName":"Doe","address":{"street":"123 Testing Way","city":"Unit Testing Ville","zip":"12345"},"age":21,"timestamp":{"date":"2000-01-01 12:00:00","timezone_type":3,"timezone":"America\/Chicago"}}',
		                    self::$sut->toJson());
	}

	/** @var Contact */
	private static $sut;
}

# endregion

# region given_a_fully_hydrated_object_with_missing_optional_value

class given_a_fully_hydrated_object_with_missing_optional_value extends BehaviorDrivenTestCase {

	protected static function given() {
		$address = new Address("123 Testing Way", "Unit Testing Ville", "12345");
		self::$sut = new Contact("John", "J", "Doe", $address);
	}
	protected static function when() {
		self::$result = self::$sut->toArray();
	}

	public function test_toArray_returns_the_optional_value_set_to_the_default_value() {

		$this->assertEquals(0, self::$result["age"]);
		$this->assertEquals(null, self::$result["timestamp"]);
	}
	public function test_toJson() {

		$this->assertEquals('{"firstName":"John","middleName":"J","lastName":"Doe","address":{"street":"123 Testing Way","city":"Unit Testing Ville","zip":"12345"},"age":0,"timestamp":null}',
		                    self::$sut->toJson());
	}

	/** @var Contact */
	private static $sut;
	private static $result = array();
}

# endregion

# region given_an_array_containing_all_the_object_properties

class given_an_array_containing_all_the_object_properties extends BehaviorDrivenTestCase {

	protected static function given() {

		$addressArr = array();
		$addressArr["street"] = "123 Testing Way";
		$addressArr["city"] = "Unit Testing Ville";
		$addressArr["zip"] = "12345";

		$timestamp = array();
		$timestamp["date"] = "2009-10-11 12:13:14";
		$timestamp["timezone"] = "America/Chicago";

		$contactArr = array();
		$contactArr["firstName"] = "John";
		$contactArr["middleName"] = "J";
		$contactArr["lastName"] = "Doe";
		$contactArr["age"] = 21;
		$contactArr["timestamp"] = $timestamp;
		$contactArr["address"] = $addressArr;

		self::$data = $contactArr;
	}
	protected static function when() {
		self::$sut = Contact::fromArray(self::$data);
	}

	public function test_fromArray_returns_a_fully_hydrated_object() {

		$this->assertEquals("John", self::$sut->getFirstName());
		$this->assertEquals("J", self::$sut->getMiddleName());
		$this->assertEquals("Doe", self::$sut->getLastName());
		$this->assertEquals("21", self::$sut->getAge());
		$this->assertEquals("2009-10-11 12:13:14", self::$sut->getTimestamp()->format("Y-m-d h:i:s"));

		$address = self::$sut->getAddress();

		$this->assertEquals("123 Testing Way", $address->getStreet());
		$this->assertEquals("Unit Testing Ville", $address->getCity());
		$this->assertEquals("12345", $address->getZip());
	}

	/** @var Contact */
	private static $sut;
	private static $data = array();
}

# endregion

# region given_an_array_that_is_missing_an_optional_property

class given_an_array_that_is_missing_an_optional_property extends BehaviorDrivenTestCase {

	protected static function given() {

		$addressArr = array();
		$addressArr["street"] = "123 Testing Way";
		$addressArr["city"] = "Unit Testing Ville";
		$addressArr["zip"] = "12345";

		$contactArr = array();
		$contactArr["firstName"] = "John";
		$contactArr["middleName"] = "J";
		$contactArr["lastName"] = "Doe";
		// Removed the optional property age
		$contactArr["address"] = $addressArr;

		self::$data = $contactArr;
	}
	protected static function when() {
		self::$sut = Contact::fromArray(self::$data);
	}

	public function test_fromArray_returns_a_fully_hydrated_object_with_the_optional_property_set_to_default_value() {

		$this->assertEquals("John", self::$sut->getFirstName());
		$this->assertEquals("J", self::$sut->getMiddleName());
		$this->assertEquals("Doe", self::$sut->getLastName());
		// Age is now set to the default value 0
		$this->assertEquals(0, self::$sut->getAge());

		$address = self::$sut->getAddress();

		$this->assertEquals("123 Testing Way", $address->getStreet());
		$this->assertEquals("Unit Testing Ville", $address->getCity());
		$this->assertEquals("12345", $address->getZip());
	}

	/** @var Contact */
	private static $sut;
	private static $data = array();
}

# endregion

# region given_an_array_that_is_missing_a_required_property

class given_an_array_that_is_missing_an_expected_property extends BehaviorDrivenTestCase {

	protected static function given() {

		$addressArr = array();
		$addressArr["street"] = "123 Testing Way";
		$addressArr["city"] = "Unit Testing Ville";
		$addressArr["zip"] = "12345";

		$contactArr = array();
		$contactArr["firstName"] = "John";
		$contactArr["middleName"] = "J";
		// Removed expected property lastName
		$contactArr["age"] = "21";
		$contactArr["address"] = $addressArr;

		self::$data = $contactArr;
	}
	protected static function when() {
		self::$sut = Contact::fromArray(self::$data);
	}

	public function test_fromArray_returns_a_fully_hydrated_object_with_the_expected_property_set_to_null() {

		$this->assertEquals("John", self::$sut->getFirstName());
		$this->assertEquals("J", self::$sut->getMiddleName());
		// Since lastName is missing the value will be set to null
		$this->assertEquals(null, self::$sut->getLastName());
		$this->assertEquals(21, self::$sut->getAge());

		$address = self::$sut->getAddress();

		$this->assertEquals("123 Testing Way", $address->getStreet());
		$this->assertEquals("Unit Testing Ville", $address->getCity());
		$this->assertEquals("12345", $address->getZip());
	}

	/** @var Contact */
	private static $sut;
	private static $data = array();
}

# endregion

# region given_a_json_that_contains_all_the_values

class given_a_json_that_contains_all_the_values extends BehaviorDrivenTestCase {

	protected static function given() {
		self::$json = '{"firstName":"John","middleName":"J","lastName":"Doe","address":{"street":"123 Testing Way", "city":"Unit Testing Ville","zip":"12345"},"age":21,"timestamp":{"date":"2009-10-11 12:13:14", "timezone_type":3,"timezone":"America\/Chicago"}}';
	}
	protected static function when() {
	}

	public function test_fromJson() {

		$contact = Contact::fromJson(self::$json);

		$this->assertEquals("John", $contact->getFirstName());
		$this->assertEquals("J", $contact->getMiddleName());
		$this->assertEquals("Doe", $contact->getLastName());
		$this->assertEquals("21", $contact->getAge());
		$this->assertEquals("2009-10-11 12:13:14", $contact->getTimestamp()->format("Y-m-d h:i:s"));

		$address = $contact->getAddress();

		$this->assertEquals("123 Testing Way", $address->getStreet());
		$this->assertEquals("Unit Testing Ville", $address->getCity());
		$this->assertEquals("12345", $address->getZip());
	}

	private static $json;
}

# endregion


# region Helper Classes

class Contact extends AbstractSerializable {

	public function getFirstName() {
		return $this->firstName;
	}
	public function getLastName() {
		return $this->lastName;
	}
	public function getMiddleName() {
		return $this->middleName;
	}
	public function getAddress() {
		return $this->address;
	}
	public function getAge() {
		return $this->age;
	}

	/**
	 * @return \DateTime
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	public function __construct($firstName, $middleName, $lastName, Address $address, $age = 0,
	                            \DateTime $timestamp = null) {

		$this->firstName = $firstName;
		$this->middleName = $middleName;
		$this->lastName = $lastName;

		$this->address = $address;
		$this->age = $age;

		$this->timestamp = $timestamp;
	}

	protected $firstName;
	protected $middleName;
	protected $lastName;
	protected $address;
	protected $age;
	/** @var \DateTime */
	protected $timestamp;
}


class Address extends AbstractSerializable {

	public function getStreet() {
		return $this->street;
	}
	public function getCity() {
		return $this->city;
	}
	public function getZip() {
		return $this->zip;
	}

	public function __construct($street, $city, $zip) {

		$this->street = $street;
		$this->city = $city;
		$this->zip = $zip;
	}

	protected $street;
	protected $city;
	protected $zip;
}

# endregion

class EOF {}
