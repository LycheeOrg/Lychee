<?php

namespace App\Rules;

use App\Constants\RandomID;
use App\Factories\AlbumFactory;
use Illuminate\Contracts\Validation\Rule;

class AlbumIDRule implements Rule
{
	protected bool $isNullable;

	public function __construct(bool $isNullable)
	{
		$this->isNullable = $isNullable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		return
			($value === null && $this->isNullable) ||
			strlen($value) === RandomID::ID_LENGTH ||
			array_key_exists($value, AlbumFactory::BUILTIN_SMARTS);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute ' .
			' must either be null, a string with ' . RandomID::ID_LENGTH . ' characters or one of the built-in IDs ' .
			implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS));
	}
}
