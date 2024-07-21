<?php

namespace App\Contracts\Http\Requests;

use App\DTO\PhotoSortingCriterion;

interface HasPhotoSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function photoSortingCriterion(): ?PhotoSortingCriterion;
}
