<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

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
