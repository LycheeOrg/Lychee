<?php

namespace App\Http\Requests\Contracts;

interface HasSortingOrder
{
	public const SORTING_ORDER_ATTRIBUTE = 'sortingOrder';

	/**
	 * @return string|null
	 */
	public function sortingOrder(): ?string;
}
