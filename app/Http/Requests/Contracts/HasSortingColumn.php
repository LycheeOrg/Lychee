<?php

namespace App\Http\Requests\Contracts;

interface HasSortingColumn
{
	const SORTING_COLUMN_ATTRIBUTE = 'sortingCol';

	/**
	 * @return string|null
	 */
	public function sortingColumn(): ?string;
}
