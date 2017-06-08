<?php

namespace Gmo\Common\Log\Processor;

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
