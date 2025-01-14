<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Constants\RandomID;
use App\Enum\SmartAlbumType;
use Illuminate\Contracts\Validation\ValidationRule;

class AlbumIDRule implements ValidationRule
{
	use ValidateTrait;

	protected bool $isNullable;

	public function __construct(bool $isNullable)
	{
		$this->isNullable = $isNullable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		return
			($value === null && $this->isNullable) ||
			strlen($value) === RandomID::ID_LENGTH ||
			SmartAlbumType::tryFrom($value) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->isNullable ? ' either null, or' : '') .
			' a string with ' . RandomID::ID_LENGTH . ' characters or one of the built-in IDs ' .
			implode(', ', SmartAlbumType::values());
	}
}
