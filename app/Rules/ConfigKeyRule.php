<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Repositories\ConfigManager;
use Illuminate\Contracts\Validation\ValidationRule;

final class ConfigKeyRule implements ValidationRule
{
	public function __construct(
		private ConfigManager $config_manager,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (is_string($value) === false) {
			$fail($attribute . ' is not a string');

			return;
		}

		/** @var string $value */
		if (!$this->config_manager->hasKey($value)) {
			$fail($attribute . ' is not a valid configuration key.');

			return;
		}
	}
}
