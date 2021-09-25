<?php

namespace App\Rules;

use App\Models\Extensions\SortingDecorator;
use Illuminate\Contracts\Validation\Rule;

class SortingRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		return
			$value === null ||
			(
				is_string($value) &&
				array_search($value, SortingDecorator::COLUMNS, true) !== false
			);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be null or one out of ' .
			implode(', ', SortingDecorator::COLUMNS);
	}
}
