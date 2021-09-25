<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ModelIDListRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		if (!is_string($value)) {
			return false;
		}
		$modelIDs = explode(',', $value);
		if (!is_array($modelIDs) || count($modelIDs) === 0) {
			return false;
		}
		$idRule = new ModelIDRule(false);
		$success = true;
		foreach ($modelIDs as $modelID) {
			$success &= $idRule->passes('', $modelID);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be a comma-seperated string of positive integers.';
	}
}
