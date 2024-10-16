<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\DTO\PhotoSortingCriterion;

interface HasSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion;
}
