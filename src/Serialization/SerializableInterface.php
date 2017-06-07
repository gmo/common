<?php

namespace Gmo\Common\Serialization;

interface SerializableInterface extends \JsonSerializable, \Serializable
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
     * @param array $obj
     *
     * @return static
     */
    public static function fromArray($obj);

    /**
     * @param string $json
     *
     * @return static
     */
    public static function fromJson($json);
}
