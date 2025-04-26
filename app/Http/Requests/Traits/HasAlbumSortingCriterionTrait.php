<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\DTO\AlbumSortingCriterion;

trait HasAlbumSortingCriterionTrait
{
	protected ?AlbumSortingCriterion $album_sorting_criterion = null;

	/**
	 * @return AlbumSortingCriterion|null
	 */
	public function albumSortingCriterion(): ?AlbumSortingCriterion
	{
		return $this->album_sorting_criterion;
	}
}
