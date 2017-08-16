<?php

namespace Gmo\Common\Log\Processor;

use Gmo\Common\Deprecated;

Deprecated::cls('\GMO\Common\Log\Processor\EnvProcessor', 1.32, '\Gmo\Web\Logger\Processor\ConstantProcessor');

/**
 * @deprecated since 1.32 and will be removed in 2.0. Use {@see \Gmo\Web\Logger\Processor\ConstantProcessor} instead.
 */
class EnvProcessor
{
    /** @var string */
    protected $env;

    /**
     * Constructor.
     *
     * @param string $env
     */
    public function __construct(string $env)
    {
        $this->env = $env;
    }

    public function __invoke(array $record)
    {
        $record['extra']['env'] = $this->env;

        return $record;
    }
}
