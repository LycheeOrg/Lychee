<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use LycheeVerify\Contract\VerifyInterface;

/**
 * This rule is designed specifically to avoid path injection.
 */
final class EnumRequireSupportRule implements ValidationRule
{
	/**
	 * Create a new rule instance.
	 *
	 * @param class-string     $type     the type of the enum
	 * @param array<int,mixed> $expected This is usually a container of allowed values for backed enum
	 * @param VerifyInterface  $verify
	 *
	 * @return void
	 */
	public function __construct(
		protected mixed $type,
		protected array $expected,
		protected VerifyInterface $verify)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if ($value === null || !enum_exists($this->type) || !method_exists($this->type, 'tryFrom')) {
			return;
		}

		try {
			// Enum version
			$value = $this->type::tryFrom($value);

			if ($value !== null && $this->isDesirable($value)) {
				return;
			}
		} catch (\TypeError) {
			return;
		}

		if ($this->verify->is_supporter()) {
			return;
		}

		$fail('Error: This functionality is only available in the Supporter Edition of Lychee. See here: https://lycheeorg.dev/get-supporter-edition/');
	}

	/**
	 * Determine if the given case is a valid case based on the only / except values.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function isDesirable($value)
	{
		return in_array(needle: $value, haystack: $this->expected, strict: true);
	}
}
