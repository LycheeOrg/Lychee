<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\JsonEncodingException;

/**
 * Base definition of a DTOclass for all Data Transfer Objects (DTO).
 *
 * A DTO is a simple object without business logic.
 * We use DTOs as result types for some controller methods which do not
 * return proper models.
 * Thereby we avoid using associative arrays and have a bit more type safety.
 *
 * @template TValue
 *
 * @extends Arrayable<string,TValue>
 */
interface DTO extends Arrayable, Jsonable, \JsonSerializable
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
	public function toJson($options = 0): string;

	/**
	 * Serializes this object into an array.
	 *
	 * @see Arrayable::toArray()
	 *
	 * @return array<mixed,mixed> The serialized properties of this object
	 *
	 * @throws \JsonException
	 */
	public function jsonSerialize(): array;

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array;
}