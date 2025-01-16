<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Controllers\Gallery\PhotoController;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * This rule is designed specifically to avoid path injection.
 */
class FileUuidRule implements DataAwareRule, ValidationRule
{
	/**
	 * All of the data under validation.
	 *
	 * @var array<string,mixed>
	 */
	protected $data = [];

	/**
	 * Set the data under validation.
	 *
	 * @param array<string,mixed> $data
	 *
	 * @phpstan-ignore-next-line
	 */
	public function setData(array $data): static
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		$value = $value === '' ? null : $value;

		if ($attribute !== 'uuid_name') {
			throw new LycheeLogicException('FileUuidRule: attribute must be "uuid_name"');
		}

		$chunk_number = intval($this->data['chunk_number'] ?? null);
		if ($chunk_number === 0) {
			return; // we are going to fail elsewhere.
		}

		if ($chunk_number === 1 && $value === null) {
			return; // it is normal that it is not set yet.
		}

		if ($chunk_number === 1 && $value !== null) {
			$fail('Error: Expected NULL in :attribute , got ' . $value . '.');

			return; // it is not normal that it is set.
		}

		if (is_string($value) === false) {
			$fail(':attribute is not a string.');

			return;
		}

		$file_name = $this->data['file_name'] ?? null;
		if ($file_name === null) {
			return; // we are going to fail elsewhere
		}

		$extension = pathinfo($file_name, PATHINFO_EXTENSION);

		$pattern = '/[a-zA-Z0-9-_]{16}\.' . $extension . '/';
		if (Str::of($value)->isMatch($pattern) === false) {
			$fail(':attribute is not a valid random string.');

			return;
		}

		if (!Storage::disk(PhotoController::DISK_NAME)->exists($value)) {
			$fail(':attribute is not a valid target file.');
		}
	}
}
