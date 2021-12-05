<?php

namespace App\Rules;

use App\Contracts\HasRandomID;
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
		return $value === null || strlen($value) === HasRandomID::ID_LENGTH;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message(): string
	{
		return ':attribute must either be null or a string with 85 characters';
	}
}
