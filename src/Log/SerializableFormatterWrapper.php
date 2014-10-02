<?php
namespace GMO\Common\Log;

use GMO\Common\Collection;
use GMO\Common\DateTime;
use GMO\Common\ISerializable;
use Monolog\Formatter\FormatterInterface;

class SerializableFormatterWrapper implements FormatterInterface {

	/**
	 * Formats a log record.
	 *
	 * @param  array $record A record to format
	 * @return mixed The formatted record
	 */
	public function format(array $record) {
		$record = $this->normalize($record);
		return $this->formatter->format($record);
	}

	/**
	 * Formats a set of log records.
	 *
	 * @param  array $records A set of records to format
	 * @return mixed The formatted set of records
	 */
	public function formatBatch(array $records) {
		foreach ($records as $key => $record) {
			$records[$key] = $this->format($record);
		}

		return $records;
	}

	protected function normalize($data) {

		// Leave DateTime objects to be converted by normalizer
        if ($data instanceof DateTime) {
            return $data;
        } elseif (is_array($data) && Collection::get($data, 'class') === 'GMO\Common\DateTime') {
            return DateTime::fromArray($data);
        }

        if ($data instanceof ISerializable) {
            $data = $data->toArray();
        }

        if (is_array($data) || $data instanceof \Traversable) {
			$normalized = array();

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

	public function __construct(FormatterInterface $formatterToWrap) {
		$this->formatter = $formatterToWrap;
	}

	protected $formatter;
}
