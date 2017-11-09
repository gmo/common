<?php

namespace Gmo\Common\Csv;

use Bolt\Collection\Bag;

trait CsvHeaderTrait
{
    /** @var bool */
    private $associativeRows;
    /** @var bool */
    private $skipFirstLine;
    /** @var Bag|null */
    private $headers;

    /**
     * Manually set the headers.
     *
     * @param string[]|iterable $headers
     * @param bool              $skipFirstLine Whether to skip the first line. If the source is missing headers,
     *                                         then this should be false. If the source has headers, that are just
     *                                         being renamed, then this should be true.
     */
    public function setHeaders(iterable $headers, bool $skipFirstLine): void
    {
        $this->headers = Bag::from($headers);
        $this->associativeRows = true;
        $this->skipFirstLine = $skipFirstLine;
    }
}
