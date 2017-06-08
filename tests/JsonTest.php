<?php

namespace Gmo\Common\Tests;

use GMO\Common\Exception\ParseException;
use GMO\Common\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testParseNull()
    {
        $this->assertNull(Json::parse(null));
    }

    public function testParseErrorDetectExtraComma()
    {
        $json = '{
        "foo": "bar",
}';
        $this->expectParseException('at line 2', $json);
    }

    public function testParseErrorDetectExtraCommaInArray()
    {
        $json = '{
        "foo": [
            "bar",
        ]
}';
        $this->expectParseException('at line 3', $json);
    }

    public function testParseErrorDetectUnescapedBackslash()
    {
        $json = '{
        "fo\o": "bar"
}';
        $this->expectParseException('at line 1', $json);
    }

    public function testParseErrorSkipsEscapedBackslash()
    {
        $json = '{
        "fo\\\\o": "bar"
        "a": "b"
}';
        $this->expectParseException('at line 2', $json);
    }

    public function testParseErrorDetectMissingQuotes()
    {
        $json = '{
        foo: "bar"
}';
        $this->expectParseException('at line 1', $json);
    }

    public function testParseErrorDetectArrayAsHash()
    {
        $json = '{
        "foo": ["bar": "baz"]
}';
        $this->expectParseException('at line 2', $json);
    }

    public function testParseErrorDetectMissingComma()
    {
        $json = '{
        "foo": "bar"
        "bar": "foo"
}';
        $this->expectParseException('at line 2', $json);
    }

    public function testParseErrorDetectMissingCommaMultiline()
    {
        $json = '{
        "foo": "barbar"
        "bar": "foo"
}';
        $this->expectParseException('at line 2', $json);
    }

    public function testParseErrorDetectMissingColon()
    {
        $json = '{
        "foo": "bar",
        "bar" "foo"
}';
        $this->expectParseException('at line 3', $json);
    }

    public function testParseErrorUtf8()
    {
        $json = "{\"message\": \"\xA4\xA6\xA8\xB4\xB8\xBC\xBD\xBE\"}";
        $this->expectParseException('Malformed UTF-8 characters', $json);
    }

    private function expectParseException($text, $json)
    {
        try {
            $result = Json::parse($json);
            $this->fail(sprintf("Parsing should have failed but didn't.\nExpected:\n\"%s\"\nFor:\n\"%s\"\nGot:\n\"%s\"", $text, $json, var_export($result, true)));
        } catch (ParseException $e) {
            $this->assertContains($text, $e->getMessage());
        }
    }

    /**
     * @requires PHP 5.4
     */
    public function testDumpSimpleJsonString()
    {
        $data = array('name' => 'composer/composer');
        $json = '{
    "name": "composer/composer"
}';
        $this->assertJsonFormat($json, $data);
    }

    /**
     * @requires PHP 5.4
     */
    public function testDumpTrailingBackslash()
    {
        $data = array('Metadata\\' => 'src/');
        $json = '{
    "Metadata\\\\": "src/"
}';
        $this->assertJsonFormat($json, $data);
    }

    /**
     * @requires PHP 5.4
     */
    public function testDumpEscape()
    {
        $data = array("Metadata\\\"" => 'src/');
        $json = '{
    "Metadata\\\\\\"": "src/"
}';
        $this->assertJsonFormat($json, $data);
    }

    /**
     * @requires PHP 5.4
     */
    public function testDumpUnicode()
    {
        if (!function_exists('mb_convert_encoding') && PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Test requires the mbstring extension');
        }

        $data = array("Žluťoučký \" kůň" => "úpěl ďábelské ódy za €");
        $json = '{
    "Žluťoučký \" kůň": "úpěl ďábelské ódy za €"
}';
        $this->assertJsonFormat($json, $data);
    }

    /**
     * @requires PHP 5.4
     */
    public function testDumpOnlyUnicode()
    {
        if (!function_exists('mb_convert_encoding') && PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Test requires the mbstring extension');
        }

        $data = "\\/ƌ";
        $this->assertJsonFormat('"\\\\\\/ƌ"', $data, JSON_UNESCAPED_UNICODE);
    }

    public function testDumpEscapedSlashes()
    {
        $data = "\\/foo";
        $this->assertJsonFormat('"\\\\\\/foo"', $data, 0);
    }

    public function testDumpEscapedBackslashes()
    {
        $data = "a\\b";
        $this->assertJsonFormat('"a\\\\b"', $data, 0);
    }

    public function testDumpEscapedUnicode()
    {
        $data = "ƌ";
        $this->assertJsonFormat('"\\u018c"', $data, 0);
    }

    private function assertJsonFormat($json, $data, $options = null)
    {
        if ($options === null) {
            $this->assertEquals($json, Json::dump($data));
        } else {
            $this->assertEquals($json, Json::dump($data, $options));
        }
    }

    /**
     * @requires PHP 5.4
     */
    public function testConvertsInvalidEncodingAsLatin9()
    {
        $result = Json::dump(array('message' => "\xA4\xA6\xA8\xB4\xB8\xBC\xBD\xBE"));
        $this->assertSame("{\n    \"message\": \"€ŠšŽžŒœŸ\"\n}", $result);
    }

    /**
     * @param $in
     * @param $expect
     * @dataProvider providesDetectAndCleanUtf8
     */
    public function testDetectAndCleanUtf8($in, $expect)
    {
        Json::detectAndCleanUtf8($in);
        $this->assertSame($expect, $in);
    }

    public function providesDetectAndCleanUtf8()
    {
        $obj = new \stdClass();

        return array(
            'null' => array(null, null),
            'int' => array(123, 123),
            'float' => array(123.45, 123.45),
            'bool false' => array(false, false),
            'bool true' => array(true, true),
            'ascii string' => array('abcdef', 'abcdef'),
            'latin9 string' => array("\xB1\x31\xA4\xA6\xA8\xB4\xB8\xBC\xBD\xBE\xFF", '±1€ŠšŽžŒœŸÿ'),
            'unicode string' => array('¤¦¨´¸¼½¾€ŠšŽžŒœŸ', '¤¦¨´¸¼½¾€ŠšŽžŒœŸ'),
            'empty array' => array(array(), array()),
            'array' => array(array('abcdef'), array('abcdef')),
            'object' => array($obj, $obj),
        );
    }
}
