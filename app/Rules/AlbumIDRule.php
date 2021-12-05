<?php

namespace App\Rules;

use App\Contracts\HasRandomID;
use App\Factories\AlbumFactory;
use Illuminate\Contracts\Validation\Rule;

class AlbumIDRule implements Rule
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
			strlen($value) === HasRandomID::ID_LENGTH ||
			array_key_exists($value, AlbumFactory::BUILTIN_SMARTS);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message(): string
	{
		return ':attribute ' .
			' must either be null, a string with ' . HasRandomID::ID_LENGTH . ' characters or one of the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
