<?php

namespace App\Rules;

use App\DTO\AlbumSortingCriterion;
use Illuminate\Contracts\Validation\Rule;

class AlbumSortingRule implements Rule
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
				array_search($value, AlbumSortingCriterion::COLUMNS, true) !== false
			);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be null or one out of ' .
			implode(', ', AlbumSortingCriterion::COLUMNS);
	}
}
