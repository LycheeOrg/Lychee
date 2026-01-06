<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\ColumnSortingPhotoType;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Repositories\ConfigManager;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoSortingCriterion extends SortingCriterion
{
	/**
	 * @return self
	 */
	public static function createDefault(): self
	{
		$config_manager = app(ConfigManager::class);
		$column_sorting = $config_manager->getValueAsEnum('sorting_photos_col', ColumnSortingPhotoType::class);
		$column_sorting = $column_sorting?->toColumnSortingType();

		$order_sorting = $config_manager->getValueAsEnum('sorting_photos_order', OrderSortingType::class);

		return new self(
			$column_sorting ?? ColumnSortingType::CREATED_AT,
			$order_sorting ?? OrderSortingType::ASC
		);
	}
}
