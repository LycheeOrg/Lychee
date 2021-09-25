<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ModelIDRule implements Rule
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
			) || (
				filter_var($value, FILTER_VALIDATE_INT) !== false &&
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
			' a positive integer';
	}
}
