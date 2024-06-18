<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\DTO\PhotoSortingCriterion;

interface HasSortingCriterion
{
	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function sortingCriterion(): ?PhotoSortingCriterion;
}
