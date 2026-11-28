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
 * `$already_granted` is whether the grant is already given on the stored
 * permission: resending it unchanged is keeping, not handing out (Q-118-09).
 * Switching the grant on (`true`) without either fails; `false` always passes.
 */
final class HeldGrantRule implements ValidationRule
{
	public function __construct(
		private bool $held,
		private bool $already_granted = false,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if ($this->held || $this->already_granted || filter_var($value, FILTER_VALIDATE_BOOLEAN) === false) {
			return;
		}

		$fail(':attribute cannot be granted without holding that right yourself.');
	}
}
