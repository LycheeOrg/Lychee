<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\DTO\UrlValidatedDTO;
use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Contracts\Validation\ValidationRule;

final class PhotoUrlRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		// Ensure the value has been pre-processed into a UrlValidatedDTO by prepareForValidation().
		if (!$value instanceof UrlValidatedDTO) {
			throw new LycheeLogicException('The value passed to the PhotoUrlRule must be an instance of UrlValidatedDTO. Got ' . get_debug_type($value));
		}

		if ($value->error !== null) {
			$fail($attribute . ' ' . $value->error);

			return;
		}

		if ($value->resolved_ip === null) {
			$fail($attribute . ' did not resolve to a valid IP address.');

			return;
		}
	}
}
