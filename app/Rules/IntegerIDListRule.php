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
		$ids = explode(',', $value);
		if (!is_array($ids) || count($ids) === 0) {
			return false;
		}
		$idRule = new IntegerIDRule(false);
		$success = true;
		foreach ($ids as $id) {
			$success &= $idRule->passes('', $id);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be list of non-zero, positive integers';
	}
}
