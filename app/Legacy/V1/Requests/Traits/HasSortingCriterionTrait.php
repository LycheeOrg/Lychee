<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

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
