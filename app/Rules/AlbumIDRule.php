<?php

namespace App\Rules;

use App\Contracts\HasRandomID;
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
			strlen($value) === HasRandomID::ID_LENGTH ||
			array_key_exists($value, AlbumFactory::BUILTIN_SMARTS);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute ' .
			' must either be null, a string with ' . HasRandomID::ID_LENGTH . ' characters or one of the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
