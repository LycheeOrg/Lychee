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
final class EnumRequireSupportRule implements ValidationRule
{
	/**
	 * Create a new rule instance.
	 *
	 * @param class-string     $type     the type of the enum
	 * @param array<int,mixed> $expected This is usually a container of allowed values for backed enum
	 * @param VerifyInterface  $verify
	 *
	 * @return void
	 */
	public function __construct(
		private mixed $type,
		private array $expected,
		private VerifyInterface $verify,
		private Status $status = Status::SUPPORTER_EDITION,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if ($value === null || !enum_exists($this->type) || !method_exists($this->type, 'tryFrom')) {
			return;
		}

		try {
			// Enum version
			$value = $this->type::tryFrom($value);

			if ($value !== null && $this->isDesirable($value)) {
				return;
			}
		} catch (\TypeError) {
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

	/**
	 * Determine if the given case is a valid case based on the only / except values.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	private function isDesirable($value)
	{
		return in_array(needle: $value, haystack: $this->expected, strict: true);
	}
}
