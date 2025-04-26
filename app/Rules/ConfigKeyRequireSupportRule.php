<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Models\Configs;
use Illuminate\Contracts\Validation\ValidationRule;
use LycheeVerify\Contract\VerifyInterface;

final class ConfigKeyRequireSupportRule implements ValidationRule
{
	use ValidateTrait;

	public function __construct(
		protected VerifyInterface $verify,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (is_string($value) === false) {
			// This is taken care of in ConfigKeyRule
			return;
		}

		/** @var string $value */
		if (!array_key_exists($value, Configs::get())) {
			// This is taken care of in ConfigKeyRule
			return;
		}

		/** @var string $value */
		$config = Configs::where('key', '=', $value)->firstOrFail();
		if ($config->level === 1 && !$this->verify->is_supporter()) {
			$fail('Error: This functionality is only available in the Supporter Edition of Lychee. See here: https://lycheeorg.dev/get-supporter-edition/');

			return;
		}
	}
}
