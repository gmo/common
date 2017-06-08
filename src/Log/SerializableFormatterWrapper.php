<?php

namespace Gmo\Common\Log;

use Gmo\Common\Serialization\SerializableCarbon;
use Gmo\Common\Serialization\SerializableInterface;
use Monolog\Formatter\FormatterInterface;

class SerializableFormatterWrapper implements FormatterInterface
{
    /** @var FormatterInterface */
    protected $formatter;

    /**
     * Constructor.
     *
     * @param FormatterInterface $formatterToWrap
     */
    public function __construct(FormatterInterface $formatterToWrap)
    {
        $this->formatter = $formatterToWrap;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record = $this->normalize($record);

        return $this->formatter->format($record);
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    protected function normalize($data)
    {
        // Leave DateTime objects to be converted by normalizer
        if ($data instanceof \DateTime) {
            return $data;
        } elseif (is_array($data) && is_a($data['class'] ?? '', 'DateTime', true)) {
            return SerializableCarbon::fromArray($data);
        }

        if ($data instanceof SerializableInterface) {
            $data = $data->toArray();
        }

        if (is_iterable($data)) {
            $normalized = [];

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ >= 1000) {
                    $normalized['...'] = 'Over 1000 items, aborting normalization';
                    break;
                }
                $normalized[$key] = $this->normalize($value);
            }

            return $normalized;
        }

        return $data;
    }
}
