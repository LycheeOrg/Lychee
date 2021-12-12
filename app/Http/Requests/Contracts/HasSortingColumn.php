<?php

namespace App\Http\Requests\Contracts;

interface HasSortingColumn
{
	public const SORTING_COLUMN_ATTRIBUTE = 'sortingCol';

	/**
	 * @return string|null
	 */
	public function sortingColumn(): ?string;
}
