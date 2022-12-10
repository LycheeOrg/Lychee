<?php

namespace App\Http\Requests\Contracts;

use App\DTO\PhotoSortingCriterion;

interface HasSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion;
}
