<?php

namespace App\Http\Requests\Traits;

use App\Data\PhotoSortingCriterion;

trait HasSortingCriterionTrait
{
	protected ?PhotoSortingCriterion $sortingCriterion = null;

	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion
	{
		return $this->sortingCriterion;
	}
}
