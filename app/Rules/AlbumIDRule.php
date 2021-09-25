<?php

namespace App\Rules;

use App\Factories\AlbumFactory;
use Illuminate\Contracts\Validation\Rule;

class AlbumIDRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		return
			$value === null ||
			(filter_var($value, FILTER_VALIDATE_INT) !== false && intval($value) >= 0) ||
			array_key_exists($value, AlbumFactory::BUILTIN_SMARTS);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute ' .
			' must either be null, a positive integer or one of the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
