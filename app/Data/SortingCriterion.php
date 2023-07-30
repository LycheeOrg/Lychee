<?php

namespace App\Data;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use Spatie\LaravelData\Data;

class SortingCriterion extends Data
{
	/**
	 * Sorting criterion.
	 *
	 * @param ColumnSortingType $column
	 * @param OrderSortingType  $order
	 *
	 * @return void
	 */
	public function __construct(
		public ColumnSortingType $column,
		public OrderSortingType $order)
	{
	}
}
