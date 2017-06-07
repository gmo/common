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

    /** @noinspection PhpUndefinedClassInspection */
    interface ISerializable extends \JsonSerializable, \Serializable
    {
        /**
         * @return array
         */
        public function toArray();

        /**
         * @return string
         */
        public function toJson();

        /**
         * @param $obj
         *
         * @return $this
         */
        public static function fromArray($obj);

        /**
         * @param $json
         *
         * @return $this
         */
        public static function fromJson($json);
    }
}
