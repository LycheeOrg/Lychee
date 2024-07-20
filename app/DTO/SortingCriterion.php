<?php

namespace App\DTO;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SortingCriterion extends ArrayableDTO
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
