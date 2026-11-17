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
 * absolute path without `.` segment, NUL byte, `..`, `%2e`, `%2f` or `\`,
 * naming an existing directory readable by PHP.
 */
final class ServerPathRule implements ValidationRule
{
	use ValidateTrait;

	public const MAX_LENGTH = 4096;

	private const FORBIDDEN_SEQUENCES = ['..', '%2e', '%2f', '\\'];

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
			!self::hasForbiddenSequence($value) &&
			!in_array('.', explode('/', $value), true) &&
			is_dir($value) &&
			is_readable($value);
	}

	/**
	 * Same sequences as SecurePathController, compared case-insensitively (`%2E`, `%2F`).
	 * `..` as a substring also covers the `..` segment.
	 */
	private static function hasForbiddenSequence(string $value): bool
	{
		$lower = strtolower($value);
		foreach (self::FORBIDDEN_SEQUENCES as $sequence) {
			if (str_contains($lower, $sequence)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be empty or the absolute path of an existing directory readable by Lychee, without "." segment, "..", "%2e", "%2f" or "\\".';
	}
}
