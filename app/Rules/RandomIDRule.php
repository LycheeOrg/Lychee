<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Constants\RandomID;
use Illuminate\Contracts\Validation\ValidationRule;
use function Safe\preg_match;

final class RandomIDRule implements ValidationRule
{
	use ValidateTrait;

	public function __construct(
		protected bool $is_nullable,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		return
			(
				$value === null &&
				$this->is_nullable
			) || preg_match('/^[-_a-zA-Z0-9]{' . RandomID::ID_LENGTH . '}$/', $value) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->is_nullable ? ' either null or' : '') .
			' a string in Base64-encoding with ' . RandomID::ID_LENGTH . ' characters';
	}
}
