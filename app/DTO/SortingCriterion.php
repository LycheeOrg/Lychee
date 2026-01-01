<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
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
