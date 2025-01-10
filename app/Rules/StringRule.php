<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class StringRule implements ValidationRule
{
	use ValidateTrait;

	protected bool $isNullable;
	protected int $limit;

	/**
	 * Constructor.
	 *
	 * @param bool $isNullable determines whether `null` is acceptable
	 * @param int  $limit      the maximum number of allowed characters; `0` means unlimited
	 */
	public function __construct(bool $isNullable, int $limit = 0)
	{
		$this->isNullable = $isNullable;
		$this->limit = $limit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		return ($value === null &&
			$this->isNullable
		) || (is_string($value) &&
			strlen($value) !== 0 &&
			($this->limit === 0 || strlen($value) <= $this->limit)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->isNullable ? ' either null or' : '') .
			' a non-empty string' .
			($this->limit !== 0 ? ' with at most ' . $this->limit . ' characters' : '');
	}
}
