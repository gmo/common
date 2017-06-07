<?php

namespace GMO\Common\Web\Exception;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Exception\MissingParameterException} instead.
 */
class MissingParameterException extends ClientException
{
    protected $statusCode = 451;

    public function __construct($key, $message = 'The %s parameter is missing')
    {
        parent::__construct(sprintf($message, "'$key'"));
    }
}
