<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Actions\Import\ImportPathGuard;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a user's `server_path` (Feature 115, FR-115-02).
 *
 * Empty or `null` disables import from server. Otherwise the value must be an
 * absolute path without `.`/`..` segments nor NUL byte, naming an existing
 * directory readable by PHP.
 */
final class ServerPathRule implements ValidationRule
{
	use ValidateTrait;

	public const MAX_LENGTH = 4096;

	/**
	 * Normalised value to store: `null` when empty, otherwise without trailing or repeated separators.
	 */
	public static function normalise(?string $value): ?string
	{
		if ($value === null || $value === '') {
			return null;
		}

		return ImportPathGuard::normalise($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		if ($value === null || $value === '') {
			return true;
		}

		return is_string($value) &&
			strlen($value) <= self::MAX_LENGTH &&
			str_starts_with($value, '/') &&
			!str_contains($value, "\0") &&
			!in_array('.', explode('/', $value), true) &&
			!in_array('..', explode('/', $value), true) &&
			is_dir($value) &&
			is_readable($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be empty or the absolute path of an existing directory readable by Lychee, without "." or ".." segments.';
	}
}
