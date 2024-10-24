<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\DTO\AlbumSortingCriterion;

interface HasAlbumSortingCriterion
{
	/**
	 * @return AlbumSortingCriterion|null
	 */
	public function albumSortingCriterion(): ?AlbumSortingCriterion;
}
