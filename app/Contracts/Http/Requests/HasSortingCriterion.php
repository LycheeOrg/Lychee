<?php

namespace App\Contracts\Http\Requests;

use App\Data\PhotoSortingCriterion;

interface HasSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion;
}
