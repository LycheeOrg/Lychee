<?php

namespace App\Http\Requests\Contracts;

use App\DTO\PhotoSortingCriterion;

interface HasSortingCriterion
{
	public const SORTING_COLUMN_ATTRIBUTE = 'sorting_column';
	public const SORTING_ORDER_ATTRIBUTE = 'sorting_order';

	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion;
}
