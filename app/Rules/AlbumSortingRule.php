<?php

namespace App\Rules;

use App\Models\Extensions\SortingDecorator;
use Illuminate\Contracts\Validation\Rule;

class AlbumSortingRule implements Rule
{
	const COLUMNS = [
		'id',
		'title',
		'description',
		'is_public',
		'max_taken_at',
		'min_taken_at',
		'created_at',
	];

	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		return
			$value === null ||
			(
				is_string($value) &&
				array_search($value, self::COLUMNS, true) !== false
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
