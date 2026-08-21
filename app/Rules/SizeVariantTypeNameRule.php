<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Enum\SizeVariantType;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a value matches a {@link SizeVariantType} case name,
 * case-insensitively.
 *
 * SizeVariantType has no built-in case-insensitive/tryFromName() lookup
 * (Feature 056), so this rule implements its own.
 */
final class SizeVariantTypeNameRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (!is_string($value) || self::resolve($value) === null) {
			$fail(':attribute must be one of: ' . implode(', ', array_map(
				static fn (SizeVariantType $case) => $case->name(),
				SizeVariantType::cases(),
			)));
		}
	}

	/**
	 * Case-insensitively resolves a token to a {@link SizeVariantType} case.
	 */
	public static function resolve(string $token): ?SizeVariantType
	{
		$needle = strtolower($token);
		foreach (SizeVariantType::cases() as $case) {
			if ($case->name() === $needle) {
				return $case;
			}
		}

		return null;
	}
}
