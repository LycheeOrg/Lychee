<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

use App\DTO\PhotoSortingCriterion;

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
