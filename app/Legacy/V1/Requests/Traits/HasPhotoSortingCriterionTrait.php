<?php

namespace App\Legacy\V1\Requests\Traits;

use App\DTO\PhotoSortingCriterion;

trait HasPhotoSortingCriterionTrait
{
	protected ?PhotoSortingCriterion $photoSortingCriterion = null;

	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function photoSortingCriterion(): ?PhotoSortingCriterion
	{
		return $this->photoSortingCriterion;
	}
}
