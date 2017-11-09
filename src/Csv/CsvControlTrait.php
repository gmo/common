<?php

namespace Gmo\Common\Csv;

use Gmo\Common\Assert;

trait CsvControlTrait
{
    /** @var string */
    private $delimiter = ',';
    /** @var string */
    private $enclosure = '"';
    /** @var string */
    private $escape = '\\';

    public function setCsvControl(string $delimiter = ',', string $enclosure = '"', string $escape = '\\'): void
    {
        Assert::length($delimiter, 1, 'Delimiter must be a single character. Got: %s');
        Assert::length($enclosure, 1, 'Enclosure must be a single character. Got: %s');
        Assert::length($escape, 1, 'Escape must be a single character. Got: %s');

        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }
}
