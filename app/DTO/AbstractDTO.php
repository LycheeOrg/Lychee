<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Contracts\DTO;
use Illuminate\Database\Eloquent\JsonEncodingException;
use function Safe\json_encode;

/**
 * Base class for all Data Transfer Objects (DTO).
 *
 * A DTO is a simple object without business logic.
 * We use DTOs as result types for some controller methods which do not
 * return proper models.
 * Thereby we avoid using associative arrays and have a bit more type safety.
 *
 * @template TValue
 *
 * @implements DTO<TValue>
 */
abstract class AbstractDTO implements DTO
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
			// Note, we must not use the option `JSON_THROW_ON_ERROR` here,
			// because this does not clear `json_last_error()` from any
			// previous, stalled error message.
			// But `\Illuminate\Http\JsonResponse::setData()` falsy assumes
			// that this method does so.
			// Hence, we call `json_encode` _without_ specifying
			// `JSON_THROW_ON_ERROR` and then mimic that behaviour.
			$json = json_encode($this->jsonSerialize(), $options);
			if (json_last_error() !== JSON_ERROR_NONE) {
				// @codeCoverageIgnoreStart
				throw new \JsonException(json_last_error_msg(), json_last_error());
				// @codeCoverageIgnoreEnd
			}

			return $json;
			// @codeCoverageIgnoreStart
		} catch (\JsonException $e) {
			throw new JsonEncodingException('Error encoding DTO [' . get_class($this) . ']', 0, $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @see Arrayable::toArray()
	 *
	 * @return array<string,TValue> The serialized properties of this object
	 *
	 * @throws \JsonException
	 */
	public function jsonSerialize(): array
	{
		try {
			return $this->toArray();
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			throw new \JsonException(get_class($this) . '::toArray() failed', 0, $e);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore
	 */
	abstract public function toArray(): array;
}
