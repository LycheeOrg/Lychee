<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Constants\RandomID;
use App\Enum\SmartAlbumType;
use Illuminate\Contracts\Validation\ValidationRule;

final class AlbumIDRule implements ValidationRule
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
			($value === null && $this->is_nullable) ||
			strlen($value) === RandomID::ID_LENGTH ||
			SmartAlbumType::tryFrom($value) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->is_nullable ? ' either null, or' : '') .
			' a string with ' . RandomID::ID_LENGTH . ' characters or one of the built-in IDs ' .
			implode(', ', SmartAlbumType::values());
	}
}
