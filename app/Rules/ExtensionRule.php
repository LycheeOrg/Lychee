<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * This rule is designed specifically to avoid path injection.
 */
class ExtensionRule implements DataAwareRule, ValidationRule
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

		if ($attribute !== 'extension') {
			throw new LycheeLogicException('ExtensionRule: attribute must be "extension"');
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

		if (Str::of($value)->isMatch('/^\.[a-zA-Z0-9]*$/') === false) {
			$fail(':attribute is not a valid extension.');

			return;
		}

		$file_name = $this->data['file_name'] ?? null;
		if ($file_name === null) {
			return; // we are going to fail elsewhere
		}

		$extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
		if ($value !== $extension) {
			$fail('Error: Expected ' . $extension . ' in :attribute, got ' . $value . '.');

			return;
		}

		$file_name = $this->data['uuid_name'] ?? null;
		if ($file_name === null) {
			return; // we are going to fail elsewhere if chunk is not 1.
		}

		$extension = '.' . pathinfo($this->data['uuid_name'] ?? '', PATHINFO_EXTENSION);
		if ($value !== $extension) {
			$fail('Error: Expected ' . $extension . ' in :attribute, got ' . $value . '.');
		}
	}
}
