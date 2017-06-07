<?php

namespace GMO\Common\Web\Exception;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Exception\NoKeyException} instead.
 */
class NoKeyException extends ClientException
{
    protected $statusCode = 441;

    public function __construct($keyName = 'key', $message = 'No %s was provided')
    {
        parent::__construct(sprintf($message, $keyName));
    }
}
