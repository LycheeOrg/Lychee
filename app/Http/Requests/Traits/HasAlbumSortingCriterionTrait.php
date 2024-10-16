<?php

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
