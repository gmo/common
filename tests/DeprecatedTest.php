<?php

namespace Gmo\Common\Tests;

use Gmo\Common\Deprecated;
use PHPUnit\Framework\TestCase;

/**
 * @author Carson Full <carsonfull@gmail.com>
 */
class DeprecatedTest extends TestCase
{
    protected $deprecations = array();

    public function testService()
    {
        Deprecated::service('foo');
        $this->assertDeprecation('Accessing $app[\'foo\'] is deprecated.');
        Deprecated::service('foo', null, 'bar');
        $this->assertDeprecation('Accessing $app[\'foo\'] is deprecated. Use $app[\'bar\'] instead.');
        Deprecated::service('foo', null, 'Do it this way instead.');
        $this->assertDeprecation('Accessing $app[\'foo\'] is deprecated. Do it this way instead.');
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
