<?php

namespace GMO\Common\Web\Exception;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Exception\InvalidKeyException} instead.
 */
class InvalidKeyException extends ClientException
{
    protected $statusCode = 442;

    public function __construct($keyName = 'key', $message = 'The %s provided is invalid')
    {
        parent::__construct(sprintf($message, $keyName));
    }
}
