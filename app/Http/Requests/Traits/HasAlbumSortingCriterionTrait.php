<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\DTO\AlbumSortingCriterion;

trait HasAlbumSortingCriterionTrait
{
	protected ?AlbumSortingCriterion $albumSortingCriterion = null;

	/**
	 * @return AlbumSortingCriterion|null
	 */
	public function albumSortingCriterion(): ?AlbumSortingCriterion
	{
		return $this->albumSortingCriterion;
	}
}
