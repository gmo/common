<?php

namespace {
    if (!interface_exists('JsonSerializable')) {
        /** @noinspection PhpUndefinedClassInspection */

        /**
         * This is a stub interface for PHP &gt;= 5.3
         *
         * Objects implementing JsonSerializable
         * can customize their JSON representation when encoded with
         * <b>json_encode</b>.
         *
         * @link http://php.net/manual/en/class.jsonserializable.php
         */
        interface JsonSerializable
        {
            /**
             * (PHP 5 &gt;= 5.4.0)<br/>
             * Specify data which should be serialized to JSON
             *
             * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
             * @return mixed data which can be serialized by <b>json_encode</b>,
             * which is a value of any type other than a resource.
             */
            function jsonSerialize();
        }
    }
}

namespace GMO\Common {

    use Gmo\Common\Serialization\SerializableInterface;

    /**
     * @deprecated will be removed in 2.0. Use {@see Gmo\Common\Serialization\SerializableInterface} instead.
     */
    interface ISerializable extends SerializableInterface
    {
        /**
         * @return string
         */
        public function toJson();

        /**
         * @param string $json
         *
         * @return static
         */
        public static function fromJson($json);
    }
}
