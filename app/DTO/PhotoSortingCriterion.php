<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\ColumnSortingPhotoType;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Configs;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoSortingCriterion extends SortingCriterion
{
	/**
	 * @return self
	 */
	public static function createDefault(): self
	{
		$column_sorting = Configs::getValueAsEnum('sorting_photos_col', ColumnSortingPhotoType::class);
		$column_sorting = $column_sorting?->toColumnSortingType();

		$order_sorting = Configs::getValueAsEnum('sorting_photos_order', OrderSortingType::class);

		return new self(
			$column_sorting ?? ColumnSortingType::CREATED_AT,
			$order_sorting ?? OrderSortingType::ASC
		);
	}
}
