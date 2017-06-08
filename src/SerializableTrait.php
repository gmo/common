<?php

namespace GMO\Common;

Deprecated::cls('GMO\Common\SerializableTrait', null, 'Gmo\Common\Serialization\SerializableTrait');

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
