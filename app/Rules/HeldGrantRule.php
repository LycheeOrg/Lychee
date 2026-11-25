<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A sharer hands out only the grants they hold (Feature 118, FR-118-07, ADR-118-01).
 *
 * `$held` is whether the current user holds the matching right
 * (`may_upload` for `grants_upload`, `may_edit` for edit, move and delete).
 * Giving the grant (`true`) without it fails; `false` always passes.
 */
final class HeldGrantRule implements ValidationRule
{
	public function __construct(
		private bool $held,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if ($this->held || filter_var($value, FILTER_VALIDATE_BOOLEAN) === false) {
			return;
		}

		$fail(':attribute cannot be granted without holding that right yourself.');
	}
}
