<?php

namespace App\Rules;

use App\Constants\RandomID;
use Illuminate\Contracts\Validation\Rule;
use function Safe\preg_match;

class RandomIDRule implements Rule
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
			(
				$value === null &&
				$this->isNullable
			) || preg_match('/^[-_a-zA-Z0-9]{' . RandomID::ID_LENGTH . '}$/', $value) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->isNullable ? ' either null or' : '') .
			' a string in Base64-encoding with ' . RandomID::ID_LENGTH . ' characters';
	}
}
