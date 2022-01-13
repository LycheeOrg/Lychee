<?php

namespace App\Rules;

use App\Contracts\HasRandomID;
use Illuminate\Contracts\Validation\Rule;

class ModelIDListRule implements Rule
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
		$modelIDs = explode(',', $value);
		$idRule = new ModelIDRule();
		$success = true;
		foreach ($modelIDs as $modelID) {
			$success &= $idRule->passes('', $modelID);
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
		return ':attribute must be a comma-seperated string of strings with ' . HasRandomID::ID_LENGTH . ' characters each.';
	}
}
