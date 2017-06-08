<?php

namespace Gmo\Common;

use Gmo\Common\Exception\DumpException;
use Gmo\Common\Exception\ParseException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * JSON parsing and dumping with error handling.
 *
 * @see https://github.com/Seldaek/monolog/pull/683
 * @see https://github.com/composer/composer/blob/1.1/src/Composer/Json/JsonFile.php
 */
class Json
{
    /**
     * Dumps an array/object into a JSON string.
     *
     * @param mixed $data    Data to encode
     * @param int   $options JSON encode options
     *                       (defaults to JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
     * @param int   $depth   Recursion depth
     *
     * @throws DumpException If dumping fails
     *
     * @return string
     */
    public static function dump($data, int $options = 448, int $depth = 512)
    {
        $json = @json_encode($data, $options, $depth);

        if ($json === false) {
            $json = static::handleJsonError($data, $options, $depth, json_last_error(), json_last_error_msg());
        }

        return $json;
    }

    /**
     * Parses a JSON string.
     *
     * @param string $json    The JSON string
     * @param int    $options Bitmask of JSON decode options
     * @param int    $depth   Recursion depth
     *
     * @throws ParseException If the JSON is not valid
     *
     * @return mixed
     */
    public static function parse(?string $json, int $options = 0, int $depth = 512)
    {
        if ($json === null) {
            return null;
        }

        $data = @json_decode($json, true, $depth, $options);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            static::determineParseError($json);
        }

        return $data;
    }

    /**
     * Handle a json_encode failure.
     *
     * @param mixed  $data    Data that was meant to be encoded
     * @param int    $options JSON encode options
     * @param int    $depth   Recursion depth
     * @param int    $code    json_last_error() code
     * @param string $message json_last_error_msg() message
     *
     * @throws DumpException If failure can't be corrected
     *
     * @return string JSON encoded data after error correction
     */
    private static function handleJsonError($data, int $options, int $depth, int $code, ?string $message)
    {
        if ($code !== JSON_ERROR_UTF8) {
            static::throwEncodeError($code, $message);
        }

        if (is_string($data)) {
            static::detectAndCleanUtf8($data);
        } elseif (is_array($data)) {
            array_walk_recursive($data, [__CLASS__, 'detectAndCleanUtf8']);
        } else {
            static::throwEncodeError($code, $message);
        }

        $json = @json_encode($data, $options, $depth);

        if ($json === false) {
            static::throwEncodeError(json_last_error(), json_last_error_msg());
        }

        return $json;
    }

    /**
     * Throws an exception according to a given code with a customized message
     *
     * @param int    $code    json_last_error() code
     * @param string $message json_last_error_msg() message
     *
     * @throws DumpException
     */
    private static function throwEncodeError(int $code, ?string $message)
    {
        throw new DumpException('JSON dumping failed: ' . ($message ?: 'Unknown error'), $code);
    }

    /**
     * Detect invalid UTF-8 string characters and convert to valid UTF-8.
     *
     * Valid UTF-8 input will be left unmodified, but strings containing
     * invalid UTF-8 codepoints will be reencoded as UTF-8 with an assumed
     * original encoding of ISO-8859-15. This conversion may result in
     * incorrect output if the actual encoding was not ISO-8859-15, but it
     * will be clean UTF-8 output and will not rely on expensive and fragile
     * detection algorithms.
     *
     * Function converts the input in place in the passed variable so that it
     * can be used as a callback for array_walk_recursive.
     *
     * @param mixed &$data Input to check and convert if needed
     *
     * @internal For PHP 5.3 compat
     */
    public static function detectAndCleanUtf8(&$data)
    {
        if (is_string($data) && !preg_match('//u', $data)) {
            $data = preg_replace_callback(
                '/[\x80-\xFF]+/',
                function ($m) { return utf8_encode($m[0]); },
                $data
            );
            $data = str_replace(
                ['¤', '¦', '¨', '´', '¸', '¼', '½', '¾'],
                ['€', 'Š', 'š', 'Ž', 'ž', 'Œ', 'œ', 'Ÿ'],
                $data
            );
        }
    }

    /**
     * Determines why JSON failed to parse and throws an informative exception.
     *
     * @param string $json
     *
     * @throws ParseException
     */
    private static function determineParseError(string $json)
    {
        $parser = new JsonParser();

        try {
            $parser->parse($json);
        } catch (ParsingException $e) {
            throw ParseException::castFromJson($e);
        }

        if (json_last_error() === JSON_ERROR_UTF8) {
            throw new ParseException('JSON parsing failed: ' . json_last_error_msg());
        }
    }
}
