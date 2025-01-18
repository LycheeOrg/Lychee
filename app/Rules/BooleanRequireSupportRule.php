<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use LycheeVerify\Contract\VerifyInterface;

/**
 * This rule is designed specifically to avoid path injection.
 */
class BooleanRequireSupportRule implements ValidationRule
{
	protected VerifyInterface $verify;
	protected bool $expected;

	public function __construct(bool $expected, VerifyInterface $verify)
	{
		$this->verify = $verify;
		$this->expected = $expected;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
		if ($value === $this->expected) {
			return;
		}

		if ($this->verify->is_supporter()) {
			return;
		}

		$fail('Error: This functionality is only available in the Supporter Edition of Lychee. See here: https://lycheeorg.dev/get-supporter-edition/');
	}
}
