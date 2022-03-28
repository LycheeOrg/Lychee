<?php

namespace App\Rules;

use App\Contracts\HasRandomID;
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
		if (!is_array($albumIDs) || count($albumIDs) === 0) {
			return false;
		}
		$idRule = new AlbumIDRule(false);
		$success = true;
		foreach ($albumIDs as $albumID) {
			$success &= $idRule->passes('', $albumID);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be a comma-separated string of strings with either ' .
			HasRandomID::ID_LENGTH . ' characters each or one of the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
