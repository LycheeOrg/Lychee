<?php

namespace App\Rules;

use App\Constants\RandomID;
use App\Factories\AlbumFactory;
use Illuminate\Contracts\Validation\Rule;

class AlbumIDListRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		if (!is_string($value)) {
			return false;
		}
		$albumIDs = explode(',', $value);
		$idRule = new AlbumIDRule(false);
		$success = true;
		foreach ($albumIDs as $albumID) {
			$success = $success && $idRule->passes('', $albumID);
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
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
