<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Constants\RandomID;
use Illuminate\Contracts\Validation\ValidationRule;

final class RandomIDListRule implements ValidationRule
{
	use ValidateTrait;

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		if (!is_string($value)) {
			return false;
		}
		$random_i_ds = explode(',', $value);
		$id_rule = new RandomIDRule(false);
		$success = true;
		foreach ($random_i_ds as $random_i_d) {
			$success = $success && $id_rule->passes('', $random_i_d);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be a comma-separated string of strings with ' . RandomID::ID_LENGTH . ' characters each.';
	}
}
