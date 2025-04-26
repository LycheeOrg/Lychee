<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

final class IntegerIDRule implements ValidationRule
{
	use ValidateTrait;

	public function __construct(
		protected bool $is_nullable,
		protected bool $is_relaxed = false,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		return (
			$value === null &&
			$this->is_nullable
		) || (
			$this->is_relaxed &&
			filter_var($value, FILTER_VALIDATE_INT) !== false &&
			intval($value) > 0
		) || (
			is_int($value) &&
			intval($value) > 0
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->is_nullable ? ' either null or' : '') .
			' a non-zero, positive integer';
	}
}
