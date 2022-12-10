<?php

namespace App\Rules;

use App\DTO\BaseSortingCriterion;
use Illuminate\Contracts\Validation\Rule;

class OrderRule implements Rule
{
	protected bool $isNullable;

	/**
	 * Constructor.
	 *
	 * @param bool $isNullable determines whether `null` is acceptable
	 */
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
			($this->isNullable && $value === null) ||
			$value === BaseSortingCriterion::ASC ||
			$value === BaseSortingCriterion::DESC;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be either ' .
			($this->isNullable ? 'null, ' : '') .
			BaseSortingCriterion::ASC .
			' or ' .
			BaseSortingCriterion::DESC;
	}
}
