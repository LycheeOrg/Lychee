<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\DTO\PhotoSortingCriterion;

interface HasPhotoSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function photoSortingCriterion(): ?PhotoSortingCriterion;
}
