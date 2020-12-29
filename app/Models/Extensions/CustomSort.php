<?php

namespace App\Models\Extensions;

use Illuminate\Support\Collection;

trait CustomSort
{
	/**
	 * Given a query, depending on the sort column, we do it in the query or on the collection.
	 * This is to be able to use natural order sorting on title and descriptions.
	 */
	public function customSort($query, $sortingCol, $sortingOrder)
	{
		if ($query == null) {
			return new Collection();
		}
		if (!in_array($sortingCol, ['title', 'description'])) {
			return $query
				->orderBy($sortingCol, $sortingOrder)
				->get();
		} else {
			return $query
				->get()
				->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
		}
	}
}
