<?php

namespace App\Http\Requests\Traits;

trait HasSortingOrderTrait
{
	protected ?string $sortingOrder = null;

	/**
	 * @return string|null
	 */
	public function sortingOrder(): ?string
	{
		return $this->sortingOrder;
	}
}
