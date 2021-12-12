<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IntegerIDListRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		if (!is_string($value)) {
			return false;
		}
		$integerIDs = explode(',', $value);
		if (!is_array($integerIDs) || count($integerIDs) === 0) {
			return false;
		}
		$idRule = new IntegerIDRule(false);
		$success = true;
		foreach ($integerIDs as $integerID) {
			$success &= $idRule->passes('', $integerID);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be a comma-seperated string of non-zero, positive integers.';
	}
}
