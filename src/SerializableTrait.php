<?php

namespace GMO\Common;

/**
 * @deprecated will be removed in 2.0.
 */
trait SerializableTrait
{
    use \Gmo\Common\Serialization\SerializableTrait;

    public function toJson()
    {
        return Json::dump($this->toArray());
    }

    public static function fromJson($json)
    {
        return static::fromArray(Json::parse($json));
    }
}
