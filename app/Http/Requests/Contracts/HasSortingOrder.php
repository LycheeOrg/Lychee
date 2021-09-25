<?php

namespace App\Http\Requests\Contracts;

interface HasSortingOrder
{
	const SORTING_ORDER_ATTRIBUTE = 'sortingOrder';

	/**
	 * @return string|null
	 */
	public function sortingOrder(): ?string;
}
