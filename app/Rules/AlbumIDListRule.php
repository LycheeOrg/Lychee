<?php

namespace App\Rules;

use App\Factories\AlbumFactory;
use Illuminate\Contracts\Validation\Rule;

class AlbumIDListRule implements Rule
{
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function passes($attribute, $value): bool
	{
		if (!is_string($value)) {
			return false;
		}
		$albumIDs = explode(',', $value);
		$albumIDRule = new AlbumIDRule();
		$success = true;
		foreach ($albumIDs as $albumID) {
			$success &= $albumIDRule->passes('', $albumID);
		}

		return $success;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message(): string
	{
		return ':attribute ' .
			' must be a comma-seperated string of positive integers or the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
