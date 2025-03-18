<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Constants\RandomID;
use App\Enum\SmartAlbumType;
use Illuminate\Contracts\Validation\ValidationRule;

class AlbumIDListRule implements ValidationRule
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
		$album_i_ds = explode(',', $value);
		$id_rule = new AlbumIDRule(false);
		$success = true;
		foreach ($album_i_ds as $album_i_d) {
			$success = $success && $id_rule->passes('', $album_i_d);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be a comma-separated string of strings with either ' .
			RandomID::ID_LENGTH . ' characters each or one of the built-in IDs ' .
			implode(', ', SmartAlbumType::values());
	}
}
