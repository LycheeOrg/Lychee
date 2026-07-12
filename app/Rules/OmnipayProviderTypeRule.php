<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Enum\OmnipayProviderType;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * This rule is designed specifically to ensure that only allowed Omnipay provider types are used.
 */
final class OmnipayProviderTypeRule implements ValidationRule
{
	public function __construct(
		private bool $allow_nullable,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if ($value === null && $this->allow_nullable) {
			return;
		}

		if (!is_string($value) && $value !== null) {
			$fail('Error: This provider must be a string.');

			return;
		}

		$provider = OmnipayProviderType::tryFrom($value ?? 'null');
		if ($provider?->isAllowed() === true) {
			return;
		}

		$value = $value === null ? 'null' : $value;

		$fail("Error: This provider {$value} is not allowed.");
	}
}
