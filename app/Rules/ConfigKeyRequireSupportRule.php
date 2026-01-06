<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Models\Configs;
use App\Repositories\ConfigManager;
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
		if (!resolve(ConfigManager::class)->hasKey($value)) {
			// This is taken care of in ConfigKeyRule
			return;
		}

		/** @var string $value */
		$config = Configs::where('key', '=', $value)->firstOrFail();
		if ($config->level === 1 && !$this->verify->is_supporter()) {
			$fail('Error: This functionality is only available in the Supporter Edition of Lychee. See here: https://lycheeorg.dev/get-supporter-edition/');

			return;
		}

		if ($config->level === 2 && !$this->verify->is_pro()) {
			$fail('Error: This functionality is only available in the Pro Edition of Lychee. See here: https://lycheeorg.dev/get-supporter-edition/');

			return;
		}
	}
}
