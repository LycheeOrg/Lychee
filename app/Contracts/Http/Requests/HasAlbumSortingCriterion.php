<?php

namespace App\Contracts\Http\Requests;

use App\DTO\AlbumSortingCriterion;

interface HasAlbumSortingCriterion
{
	/**
	 * @return AlbumSortingCriterion|null
	 */
	public function albumSortingCriterion(): ?AlbumSortingCriterion;
}
