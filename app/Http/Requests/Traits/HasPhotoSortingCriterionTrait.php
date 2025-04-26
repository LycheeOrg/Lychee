<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\DTO\PhotoSortingCriterion;

trait HasPhotoSortingCriterionTrait
{
	protected ?PhotoSortingCriterion $photo_sorting_criterion = null;

	/**
	 * @return PhotoSortingCriterion|null
	 */
	public function photoSortingCriterion(): ?PhotoSortingCriterion
	{
		return $this->photo_sorting_criterion;
	}
}
