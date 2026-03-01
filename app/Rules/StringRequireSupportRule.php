<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use LycheeVerify\Contract\Status;
use LycheeVerify\Contract\VerifyInterface;

/**
 * This rule is designed specifically to avoid path injection.
 */
final class StringRequireSupportRule implements ValidationRule
{
	public function __construct(
		private mixed $expected,
		private VerifyInterface $verify,
		private Status $status = Status::SUPPORTER_EDITION,
	) {
		$this->verify = $verify;
		$this->expected = $expected === '' ? null : $expected;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		$value = $value === '' ? null : $value;
		if ($value === $this->expected) {
			return;
		}

		if ($this->verify->check($this->status)) {
			return;
		}

		$edition = match ($this->status) {
			Status::SUPPORTER_EDITION => 'Supporter Edition',
			Status::PRO_EDITION => 'Pro Edition',
			Status::SIGNATURE_EDITION => 'Signature Edition',
			default => 'Unknown Edition',
		};

		$fail('Error: This functionality is only available in the ' . $edition . ' of Lychee. See here: https://lycheeorg.dev/get-supporter-edition/');
	}
}
