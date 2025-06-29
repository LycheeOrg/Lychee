<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Exceptions\UnauthorizedException;
use App\Models\Configs;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

final class OwnerConfigRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if ($value !== 'owner_id') {
			return;
		}

		if (Configs::getValueAsInt('owner_id') !== Auth::id()) {
			throw new UnauthorizedException('Only the owner can change the owner_id configuration.');
		}
	}
}
