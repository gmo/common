<?php

namespace GMO\Common;

Deprecated::cls('GMO\Common\SerializeHelper', null, 'Gmo\Common\Serialization\SerializeHelper');

/**
 * This class was refactored from {@see AbstractSerializable} to make it easier when you
 * need to implement {@see ISerializable} without extending {@see AbstractSerializable}
 *
 * @deprecated will be removed in 2.0. Use {@see AbstractSerializable} or {@see SerializableTrait} instead.
 */
class SerializeHelper extends \Gmo\Common\Serialization\SerializeHelper
{
}
