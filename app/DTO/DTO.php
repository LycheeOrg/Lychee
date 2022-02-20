<?php

namespace App\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\JsonEncodingException;

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
	 * Convert the instance into a JSON string.
	 *
	 * The error message is inspired by {@link JsonEncodingException::forModel()}.
	 *
	 * @param int $options
	 *
	 * @return string
	 *
	 * @throws JsonEncodingException
	 */
	public function toJson($options = 0): string
	{
		try {
			return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			throw new JsonEncodingException('Error encoding DTO [' . get_class($this) . ']', 0, $e);
		}
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @see Arrayable::toArray()
	 *
	 * @return array The serialized properties of this object
	 *
	 * @throws \JsonException
	 */
	public function jsonSerialize(): array
	{
		try {
			return $this->toArray();
		} catch (\Exception $e) {
			throw new \JsonException(get_class($this) . '::toArray() failed', 0, $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	abstract public function toArray(): array;
}
