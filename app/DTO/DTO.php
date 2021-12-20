<?php

namespace App\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Base class for all Data Transfer Objects (DTO).
 *
 * A DTO is a simple object without business logic.
 * We use DTOs as result types for some controller methods which do not
 * return proper models.
 * Thereby we avoid using associative arrays and have a bit more type safety.
 */
abstract class DTO implements Arrayable, Jsonable, \JsonSerializable
{
	/**
	 * Convert the model instance to JSON.
	 *
	 * @param int $options
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function toJson($options = 0): string
	{
		$json = json_encode($this->jsonSerialize(), $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new \RuntimeException('Could not serialize ' . get_class($this) . ': ' . json_last_error_msg());
		}

		return $json;
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
