<?php

namespace Gmo\Common\Csv;

use Bolt\Common\Thrower;
use Gmo\Common\Iterator\LineIterator;
use Http\Discovery\StreamFactoryDiscovery;
use Psr\Http\Message\StreamInterface;

/**
 * A CSV file abstraction.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class CsvFile extends \SplFileInfo implements \IteratorAggregate
{
    use CsvControlTrait;
    use CsvHeaderTrait;

    /** @var CsvIterator */
    private $iterator;

    /**
     * Constructor.
     *
     * @param string $fileName          The file to open
     * @param bool   $associativeRows   Whether to read and write rows with their headers as keys
     */
    public function __construct(string $fileName, bool $associativeRows = false)
    {
        parent::__construct($fileName);

        $this->associativeRows = $associativeRows;
        $this->skipFirstLine = $associativeRows;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if ($this->iterator) {
            return $this->iterator;
        }

        $stream = $this->openStream('rb');
        $it = new LineIterator($stream);

        $it = new CsvIterator($it);
        $it->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        if ($this->associativeRows) {
            $it = new AssociativeCsvIterator($it);
            if ($this->headers) {
                $it->setHeaders($this->headers, $this->skipFirstLine);
            }
        }

        return $this->iterator = $it;
    }

    /**
     * Create a stream for the file.
     *
     * @param string $mode
     *
     * @return StreamInterface
     */
    public function openStream(string $mode): StreamInterface
    {
        try {
            $res = Thrower::call('fopen', $this, $mode);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to open %s using mode "%s"', $this, $mode), 0, $e);
        }

        return StreamFactoryDiscovery::find()->createStream($res);
    }

    /**
     * Create a writer to be used externally.
     *
     * @param bool $append
     *
     * @return CsvWriterInterface
     */
    public function createWriter(bool $append = true): CsvWriterInterface
    {
        $stream = $this->openStream($append ? 'a+' : 'w+');

        $writer = new CsvStreamWriter($stream);
        $writer->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        if ($this->associativeRows) {
            $writer = new AssociativeWriterDecorator($writer);
            if ($this->headers) {
                $writer->setHeaders($this->headers);
            }
        }

        return $writer;
    }
}
