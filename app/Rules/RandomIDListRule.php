<?php

namespace App\Rules;

use App\Contracts\HasRandomID;
use Illuminate\Contracts\Validation\Rule;

class RandomIDListRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
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
		return ':attribute must be a comma-separated string of strings with ' . HasRandomID::ID_LENGTH . ' characters each.';
	}
}
