<?php

namespace App\Contracts\Http\Requests;

use App\DTO\PhotoSortingCriterion;

interface HasSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion;
}
