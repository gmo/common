<?php

namespace Gmo\Common\Tests;

use GMO\Common\Deprecated;
use PHPUnit\Framework\TestCase;

/**
 * @author Carson Full <carsonfull@gmail.com>
 */
class DeprecatedTest extends TestCase
{
    protected $deprecations = array();

    public function testMethod()
    {
        Deprecated::method(1.1, 'baz', 'Foo::bar');
        $this->assertDeprecation('Foo::bar() is deprecated since 1.1 and will be removed in 2.0. Use baz() instead.');

        $realClass = __CLASS__;
        Deprecated::method(1.1, $realClass, 'Foo::bar');
        $this->assertDeprecation("Foo::bar() is deprecated since 1.1 and will be removed in 2.0. Use $realClass instead.");

        Deprecated::method(1.1, 'Do it this way instead.', 'Foo::bar');
        $this->assertDeprecation('Foo::bar() is deprecated since 1.1 and will be removed in 2.0. Do it this way instead.');
    }

    public function testMethodUsingBacktrace()
    {
        TestDeprecatedClass::foo();
        $this->assertDeprecation('Gmo\Common\Tests\TestDeprecatedClass::foo() is deprecated since 1.1 and will be removed in 2.0.');

        deprecatedFunction();
        $this->assertDeprecation('Gmo\Common\Tests\deprecatedFunction() is deprecated since 1.1 and will be removed in 2.0.');

        TestDeprecatedClass::magicStatic();
        $this->assertDeprecation('Gmo\Common\Tests\TestDeprecatedClass::magicStatic() is deprecated since 1.1 and will be removed in 2.0.');

        $cls = new TestDeprecatedClass();
        $cls->magic();
        $this->assertDeprecation('Gmo\Common\Tests\TestDeprecatedClass::magic() is deprecated since 1.1 and will be removed in 2.0.');

        $cls->magic;
        $this->assertDeprecation('Getting Gmo\Common\Tests\TestDeprecatedClass::magic is deprecated since 1.1 and will be removed in 2.0.');

        $cls->magic = 'derp';
        $this->assertDeprecation('Setting Gmo\Common\Tests\TestDeprecatedClass::magic is deprecated since 1.1 and will be removed in 2.0.');

        isset($cls->magic);
        $this->assertDeprecation('isset(Gmo\Common\Tests\TestDeprecatedClass::magic) is deprecated since 1.1 and will be removed in 2.0.');
        unset($cls->magic);
        $this->assertDeprecation('unset(Gmo\Common\Tests\TestDeprecatedClass::magic) is deprecated since 1.1 and will be removed in 2.0.');

        new TestDeprecatedClass(true);
        $this->assertDeprecation('Gmo\Common\Tests\TestDeprecatedClass is deprecated since 1.1 and will be removed in 2.0.');
    }

    public function testClass()
    {
        Deprecated::cls('Foo\Bar');
        $this->assertDeprecation('Foo\Bar is deprecated.');
        Deprecated::cls('Foo\Bar', 1.1);
        $this->assertDeprecation('Foo\Bar is deprecated since 1.1 and will be removed in 2.0.');
        Deprecated::cls('Foo\Bar', null, 'Bar\Baz');
        $this->assertDeprecation('Foo\Bar is deprecated. Use Bar\Baz instead.');
        Deprecated::cls('Foo\Bar', null, 'Do it this way instead.');
        $this->assertDeprecation('Foo\Bar is deprecated. Do it this way instead.');
    }

    public function testService()
    {
        Deprecated::service('foo');
        $this->assertDeprecation('Accessing $app[\'foo\'] is deprecated.');
        Deprecated::service('foo', null, 'bar');
        $this->assertDeprecation('Accessing $app[\'foo\'] is deprecated. Use $app[\'bar\'] instead.');
        Deprecated::service('foo', null, 'Do it this way instead.');
        $this->assertDeprecation('Accessing $app[\'foo\'] is deprecated. Do it this way instead.');
    }

    public function testWarn()
    {
        Deprecated::warn('Foo bar');
        $this->assertDeprecation('Foo bar is deprecated.');

        Deprecated::warn('Foo bar', 1.0);
        $this->assertDeprecation('Foo bar is deprecated since 1.0 and will be removed in 2.0.');
        Deprecated::warn('Foo bar', 1.3);
        $this->assertDeprecation('Foo bar is deprecated since 1.3 and will be removed in 2.0.');

        Deprecated::warn('Foo bar', null, 'Use baz instead.');
        $this->assertDeprecation('Foo bar is deprecated. Use baz instead.');
        Deprecated::warn('Foo bar', 1.0, 'Use baz instead.');
        $this->assertDeprecation('Foo bar is deprecated since 1.0 and will be removed in 2.0. Use baz instead.');
    }

    public function testRaw()
    {
        Deprecated::raw('Hello world.');
        $this->assertDeprecation('Hello world.');
    }

    protected function setUp()
    {
        $this->deprecations = array();

        $deprecations = &$this->deprecations;
        set_error_handler(
            function ($type, $msg, $file, $line) use (&$deprecations) {
                $deprecations[] = $msg;
            },
            E_USER_DEPRECATED
        );
    }

    protected function tearDown()
    {
        restore_error_handler();
    }

    private function assertDeprecation($msg)
    {
        $this->assertNotEmpty($this->deprecations, 'No deprecations triggered.');
        $this->assertStringMatchesFormat($msg, $this->deprecations[0]);
        $this->deprecations = array();
    }
}

class TestDeprecatedClass
{
    public function __construct($deprecatedClass = false)
    {
        if ($deprecatedClass) {
            Deprecated::method(1.1);
        }
    }

    public static function foo()
    {
        Deprecated::method(1.1);
    }

    public function __call($name, $arguments)
    {
        Deprecated::method(1.1);
    }

    public static function __callStatic($name, $arguments)
    {
        Deprecated::method(1.1);
    }

    public function __get($name)
    {
        Deprecated::method(1.1);
    }

    public function __set($name, $value)
    {
        Deprecated::method(1.1);
    }

    public function __isset($name)
    {
        Deprecated::method(1.1);
    }

    public function __unset($name)
    {
        Deprecated::method(1.1);
    }
}

function deprecatedFunction()
{
    Deprecated::method(1.1);
}
