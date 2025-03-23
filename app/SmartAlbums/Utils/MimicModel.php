<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\SmartAlbums\Utils;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use Illuminate\Support\Str;

trait MimicModel
{
	abstract public function toArray();

	/**
	 * Gets a property dynamically.
	 *
	 * This method is inspired by
	 * {@link \Illuminate\Database\Eloquent\Model::__get()}
	 * and enables the using class to be treated the same way as real models.
	 *
	 * @param string $key
	 *
	 * @throws InternalLycheeException
	 */
	public function __get(mixed $key)
	{
		if ($key === '') {
			throw new LycheeInvalidArgumentException('property name must not be empty');
		}

		$studly_key = Str::studly($key);
		$getter = 'get' . $studly_key . 'Attribute';
		$studly_key = lcfirst($studly_key);

		if (method_exists($this, $getter)) {
			/** @phpstan-ignore-next-line PhpStan does not like variadic calls */
			return $this->{$getter}();
		} elseif (property_exists($this, $key)) {
			/** @phpstan-ignore-next-line PhpStan does not like variadic calls */
			return $this->{$key};
		} elseif (property_exists($this, $studly_key)) {
			/** @phpstan-ignore-next-line PhpStan does not like variadic calls */
			return $this->{$studly_key};
		} else {
			throw new LycheeInvalidArgumentException('neither property nor getter method exist for [' . $getter . '/' . $key . '/' . $studly_key . ']');
		}
	}

	/**
	 * Determine if the given relation is loaded.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function relationLoaded($key)
	{
		return $key === 'photos' && $this->photos !== null;
	}
}