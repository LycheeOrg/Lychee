<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Exceptions\UnauthorizedException;
use App\Repositories\ConfigManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

final class OwnerIdRule implements ValidationRule
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
		if ($this->config_manager->getValueAsInt('owner_id') !== intval($value)) {
			return;
		}

		if (Auth::id() === $this->config_manager->getValueAsInt('owner_id')) {
			return;
		}

		throw new UnauthorizedException('Only the owner can do this.');
	}
}
