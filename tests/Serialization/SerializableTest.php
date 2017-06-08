<?php

namespace Gmo\Common\Tests\Serialization;

use Gmo\Common\Serialization\AbstractSerializable;
use PHPUnit\Framework\TestCase;

class SerializableTest extends TestCase
{
    public function testToArray()
    {
        $contact = $this->getContact();

        $result = $contact->toArray();

        $this->assertSame('Gmo\Common\Tests\Serialization\Contact', $result['class']);
        $this->assertSame('John', $result['firstName']);
        $this->assertSame('J', $result['middleName']);
        $this->assertSame('Doe', $result['lastName']);
        $this->assertSame(21, $result['age']);
        $timestamp = $result['timestamp'];
        $this->assertSame('Gmo\Common\Serialization\SerializableCarbon', $timestamp['class']);
        $this->assertSame('2009-10-11 12:13:14.000000', $timestamp['date']);

        $address = $result["address"];
        $this->assertSame('Gmo\Common\Tests\Serialization\Address', $address['class']);
        $this->assertSame("123 Testing Way", $address["street"]);
        $this->assertSame("Unit Testing Ville", $address["city"]);
        $this->assertSame("12345", $address["zip"]);
    }

    public function testLegacyToJson()
    {
        $contact = $this->getContact();

        $serialized = <<<JSON
{
    "class": "Gmo\\\\Common\\\\Tests\\\\Serialization\\\\Contact",
    "firstName": "John",
    "middleName": "J",
    "lastName": "Doe",
    "address": {
        "class": "Gmo\\\\Common\\\\Tests\\\\Serialization\\\\Address",
        "street": "123 Testing Way",
        "city": "Unit Testing Ville",
        "zip": "12345"
    },
    "age": 21,
    "timestamp": {
        "class": "Gmo\\\\Common\\\\Serialization\\\\SerializableCarbon",
        "date": "2009-10-11 12:13:14.000000",
        "timezone_type": 3,
        "timezone": "America/Chicago"
    }
}
JSON;

        $this->assertEquals($serialized, $contact->toJson());
    }

    public function testToArrayOptionalValuesAreDefaulted()
    {
        $contact = new Contact('John', 'J', 'Doe', $this->getAddress());

        $result = $contact->toArray();

        $this->assertSame(0, $result['age']);
        $this->assertNull($result['timestamp']);
    }

    public function testFromArray()
    {
        $contact = Contact::fromArray($this->getContactArray());

        $this->assertSame('John', $contact->getFirstName());
        $this->assertSame('J', $contact->getMiddleName());
        $this->assertSame('Doe', $contact->getLastName());
        $this->assertSame(21, $contact->getAge());

        $this->assertSame('2009-10-11 12:13:14', $contact->getTimestamp()->format('Y-m-d h:i:s'));

        $this->assertSame('123 Testing Way', $contact->getAddress()->getStreet());
        $this->assertSame('Unit Testing Ville', $contact->getAddress()->getCity());
        $this->assertSame('12345', $contact->getAddress()->getZip());
    }

    public function testFromArrayOptionalValuesAreDefaulted()
    {
        $contactArr = $this->getContactArray();
        unset($contactArr['age']);
        $contact = Contact::fromArray($contactArr);

        $this->assertSame('John', $contact->getFirstName());
        $this->assertSame('J', $contact->getMiddleName());
        $this->assertSame('Doe', $contact->getLastName());
        $this->assertSame(0, $contact->getAge());

        $this->assertSame('2009-10-11 12:13:14', $contact->getTimestamp()->format('Y-m-d h:i:s'));

        $this->assertSame('123 Testing Way', $contact->getAddress()->getStreet());
        $this->assertSame('Unit Testing Ville', $contact->getAddress()->getCity());
        $this->assertSame('12345', $contact->getAddress()->getZip());
    }

    public function testFromArrayMissingRequiredValuesAreNull()
    {
        $contactArr = $this->getContactArray();
        unset($contactArr['lastName']);
        $contact = Contact::fromArray($contactArr);

        $this->assertSame('John', $contact->getFirstName());
        $this->assertSame('J', $contact->getMiddleName());
        $this->assertNull($contact->getLastName());
        $this->assertSame(21, $contact->getAge());

        $this->assertSame('2009-10-11 12:13:14', $contact->getTimestamp()->format('Y-m-d h:i:s'));

        $this->assertSame('123 Testing Way', $contact->getAddress()->getStreet());
        $this->assertSame('Unit Testing Ville', $contact->getAddress()->getCity());
        $this->assertSame('12345', $contact->getAddress()->getZip());
    }

    public function testLegacyFromJson()
    {
        $json = '{"firstName":"John","middleName":"J","lastName":"Doe","address":{"street":"123 Testing Way", "city":"Unit Testing Ville","zip":"12345"},"age":21,"timestamp":{"date":"2009-10-11 12:13:14.000000", "timezone_type":3,"timezone":"America\/Chicago"}}';
        $contact = Contact::fromJson($json);

        $this->assertSame('John', $contact->getFirstName());
        $this->assertSame('J', $contact->getMiddleName());
        $this->assertSame('Doe', $contact->getLastName());
        $this->assertSame(21, $contact->getAge());

        $this->assertSame('2009-10-11 12:13:14', $contact->getTimestamp()->format('Y-m-d h:i:s'));

        $this->assertSame('123 Testing Way', $contact->getAddress()->getStreet());
        $this->assertSame('Unit Testing Ville', $contact->getAddress()->getCity());
        $this->assertSame('12345', $contact->getAddress()->getZip());
    }

    /**
     * @expectedException \GMO\Common\Exception\NotSerializableException
     * @expectedExceptionMessage Gmo\Common\Tests\Serialization\Herp does not implement Gmo\Common\Serialization\SerializableInterface
     */
    public function testNotSerializable()
    {
        $derp = new Derp(new Herp());
        Derp::fromArray($derp->toArray());
    }

    private function getContact()
    {
        return new Contact('John', 'J', 'Doe', $this->getAddress(), 21, new \DateTime('2009-10-11 12:13:14', new \DateTimeZone('America/Chicago')));
    }

    private function getAddress()
    {
        return new Address('123 Testing Way', 'Unit Testing Ville', '12345');
    }

    private function getContactArray()
    {
        return array(
            'firstName'  => 'John',
            'middleName' => 'J',
            'lastName'   => 'Doe',
            'age'        => 21,
            'timestamp'  => array(
                'date'     => '2009-10-11 12:13:14',
                'timezone' => 'America/Chicago',
            ),
            'address'    => array(
                'street' => '123 Testing Way',
                'city'   => 'Unit Testing Ville',
                'zip'    => '12345',
            ),
        );
    }
}

class Contact extends AbstractSerializable
{
    protected $firstName;
    protected $middleName;
    protected $lastName;
    protected $address;
    protected $age;
    /** @var \DateTime */
    protected $timestamp;

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getMiddleName()
    {
        return $this->middleName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getAge()
    {
        return $this->age;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function __construct(
        $firstName,
        $middleName,
        $lastName,
        Address $address,
        $age = 0,
        \DateTime $timestamp = null
    ) {
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;

        $this->address = $address;
        $this->age = $age;

        $this->timestamp = $timestamp;
    }
}

class Address extends AbstractSerializable
{
    protected $street;
    protected $city;
    protected $zip;

    public function __construct($street, $city, $zip)
    {
        $this->street = $street;
        $this->city = $city;
        $this->zip = $zip;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getZip()
    {
        return $this->zip;
    }
}

class Herp
{
}

class Derp extends AbstractSerializable
{
    protected $herp;

    public function __construct(Herp $herp)
    {
        $this->herp = $herp;
    }
}
