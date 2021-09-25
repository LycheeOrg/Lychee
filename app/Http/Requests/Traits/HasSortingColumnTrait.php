<?php

namespace App\Http\Requests\Traits;

trait HasSortingColumnTrait
{
	protected ?string $sortingColumn = null;

	/**
	 * @return string|null
	 */
	public function sortingColumn(): ?string
	{
		return $this->sortingColumn;
	}
}
