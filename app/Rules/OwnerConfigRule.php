<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Exceptions\UnauthorizedException;
use App\Repositories\ConfigManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

final class OwnerConfigRule implements ValidationRule
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
		if ($value !== 'owner_id') {
			return;
		}

		if ($this->config_manager->getValueAsInt('owner_id') !== Auth::id()) {
			throw new UnauthorizedException('Only the owner can change the owner_id configuration.');
		}
	}
}
