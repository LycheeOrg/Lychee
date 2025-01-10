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
	abstract public function toArray(): array;

	/**
	 * Gets a property dynamically.
	 *
	 * This method is inspired by
	 * {@link \Illuminate\Database\Eloquent\Model::__get()}
	 * and enables the using class to be treated the same way as real models.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @throws InternalLycheeException
	 */
	public function __get(string $key)
	{
		if ($key === '') {
			throw new LycheeInvalidArgumentException('property name must not be empty');
		}

		$studlyKey = Str::studly($key);
		$getter = 'get' . $studlyKey . 'Attribute';
		$studlyKey = lcfirst($studlyKey);

		if (method_exists($this, $getter)) {
			/** @phpstan-ignore-next-line PhpStan does not like variadic calls */
			return $this->{$getter}();
		} elseif (property_exists($this, $key)) {
			/** @phpstan-ignore-next-line PhpStan does not like variadic calls */
			return $this->{$key};
		} elseif (property_exists($this, $studlyKey)) {
			/** @phpstan-ignore-next-line PhpStan does not like variadic calls */
			return $this->{$studlyKey};
		} else {
			throw new LycheeInvalidArgumentException('neither property nor getter method exist for [' . $getter . '/' . $key . '/' . $studlyKey . ']');
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
