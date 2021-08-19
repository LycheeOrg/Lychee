<?php

namespace App\Rules;

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
			(filter_var($value, FILTER_VALIDATE_INT) !== false && intval($value) >= 0) ||
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
			' must either be null, a positive integer or one of the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
