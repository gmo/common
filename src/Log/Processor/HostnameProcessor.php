<?php

namespace GMO\Common\Log\Processor;

/**
 * Adds the system's hostname to records
 */
class HostnameProcessor
{
    /** @var string */
    protected $hostname;

    /**
     * Constructor.
     *
     * @param string $hostname
     */
    public function __construct($hostname = null)
    {
        $this->hostname = $hostname ?: gethostname();
    }

    public function __invoke(array $record)
    {
        $record['extra']['host'] = $this->hostname;

        return $record;
    }
}
