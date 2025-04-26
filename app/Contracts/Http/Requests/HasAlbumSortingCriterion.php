<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\DTO\AlbumSortingCriterion;

interface HasAlbumSortingCriterion
{
	/**
	 * @return AlbumSortingCriterion|null
	 */
	public function albumSortingCriterion(): ?AlbumSortingCriterion;
}
