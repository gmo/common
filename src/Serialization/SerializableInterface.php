<?php

namespace Gmo\Common\Serialization;

interface SerializableInterface extends \JsonSerializable, \Serializable
{
    /**
     * @return array
     */
    public function toArray();

    /**
     * @param array $obj
     *
     * @return static
     */
    public static function fromArray($obj);
}
