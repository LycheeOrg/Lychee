<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IntegerIDRule implements Rule
{
	protected bool $isNullable;
	protected bool $isRelaxed;

	public function __construct(bool $isNullable, bool $isRelaxed = false)
	{
		$this->isNullable = $isNullable;
		$this->isRelaxed = $isRelaxed;
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
			) || (
				$this->isRelaxed &&
				filter_var($value, FILTER_VALIDATE_INT) !== false &&
				intval($value) > 0
			) || (
				is_int($value) &&
				intval($value) > 0
			);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be' .
			($this->isNullable ? ' either null or' : '') .
			' a non-zero, positive integer';
	}
}
