<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Exceptions\ModelDBException;
use Illuminate\Support\Str;

/**
 * Fixed Eloquent model for all Lychee models.
 *
 * This trait wraps some Eloquent model methods to make error handling
 * more consistent.
 * Some Eloquent methods report error conditions by using a mix of returning
 * `false` or throwing an exception.
 * This makes proper error handling a tedious task, because we always have
 * to check for two possible conditions.
 * This model unifies error reporting and also throws an exception when
 * the original parent method would return `false`.
 */
trait ThrowsConsistentExceptions
{
	protected function friendlyModelName(): string
	{
		$name = Str::snake(class_basename($this), ' ');

		// Remove some typical, implementation-specific pre- and suffixes from the name
		return str_replace('/(^abstract )|( impl$)|( interface$)/', '', $name);
	}

	/**
	 * Converts the instance into an (associative) array.
	 *
	 * @internal Note, that this method must not declare a return type.
	 *           The signature of this method must be compatible to
	 *           {@link \Illuminate\Database\Eloquent\Model::toArray()} which
	 *           neither declares a return type.
	 *           Otherwise, PHP will fail with a fatal parsing error.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract public function toArray();

	/**
	 * @param array<string,bool> $options
	 *
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 *
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function save(array $options = []): bool
	{
		$parentException = null;
		try {
			// Note, `Model::save` may also return `null` which also indicates a success
			if (parent::save($options) === false) {
				$parentException = new \RuntimeException('Eloquent\Model::save() returned false');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException !== null) {
			throw ModelDBException::create($this->friendlyModelName(), $this->wasRecentlyCreated ? 'creating' : 'updating', $parentException);
		}

		return true;
	}

	/**
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 *
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function delete(): bool
	{
		$parentException = null;
		try {
			// Sic! Don't use `!$parentDelete` in condition, because we also
			// need to proceed if `$parentDelete === null` .
			// If Eloquent returns `null` (instead of `true`), this also
			// indicates a success, and we must go on.
			// Eloquent, I love you .... not.
			$result = parent::delete();
			if ($result === false) {
				$parentException = new \RuntimeException('Eloquent\Model::delete() returned false');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException !== null) {
			throw ModelDBException::create($this->friendlyModelName(), 'deleting', $parentException);
		}

		return true;
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array<string,mixed> The serialized properties of this object
	 *
	 * @throws \JsonException
	 *
	 * @see ThrowsConsistentExceptions::toArray()
	 */
	public function jsonSerialize(): array
	{
		try {
			return $this->toArray();
		} catch (\Exception $e) {
			throw new \JsonException(get_class($this) . '::toArray() failed', 0, $e);
		}
	}
}
