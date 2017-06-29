<?php

namespace Gmo\Common\Serialization;

/**
 * @deprecated will be removed in 2.0.
 */
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
