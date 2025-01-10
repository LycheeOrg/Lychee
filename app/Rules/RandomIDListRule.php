<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Constants\RandomID;
use Illuminate\Contracts\Validation\ValidationRule;

class RandomIDListRule implements ValidationRule
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
		$randomIDs = explode(',', $value);
		$idRule = new RandomIDRule(false);
		$success = true;
		foreach ($randomIDs as $randomID) {
			$success = $success && $idRule->passes('', $randomID);
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
