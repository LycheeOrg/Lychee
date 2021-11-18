<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ModelIDRule implements Rule
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
		return
			$value === null ||
			(filter_var($value, FILTER_VALIDATE_INT) !== false && intval($value) >= 0);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message(): string
	{
		return ':attribute must either be null or an positive integer.';
	}
}
