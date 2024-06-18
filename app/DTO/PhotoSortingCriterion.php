<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\ColumnSortingPhotoType;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Configs;

class PhotoSortingCriterion extends SortingCriterion
{
	/**
	 * @return self
	 */
	public static function createDefault(): self
	{
		$columnSorting = Configs::getValueAsEnum('sorting_photos_col', ColumnSortingPhotoType::class);
		$columnSorting = $columnSorting?->toColumnSortingType();

		$orderSorting = Configs::getValueAsEnum('sorting_photos_order', OrderSortingType::class);

		return new self(
			$columnSorting ?? ColumnSortingType::CREATED_AT,
			$orderSorting ?? OrderSortingType::ASC
		);
	}
}
