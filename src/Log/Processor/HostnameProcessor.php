<?php

namespace GMO\Common\Log\Processor;

use Gmo\Common\Deprecated;

Deprecated::cls('\GMO\Common\Log\Processor\HostnameProcessor', 1.32, '\Gmo\Web\Logger\Processor\ConstantProcessor');

/**
 * Adds the system's hostname to records
 *
 * @deprecated since 1.32 and will be removed in 2.0. Use {@see \Gmo\Web\Logger\Processor\ConstantProcessor} instead.
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
