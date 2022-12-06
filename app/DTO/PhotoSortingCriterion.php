<?php

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
		$columSortingString = Configs::getValueAsString('sorting_photos_col');
		$columnSorting = ColumnSortingPhotoType::tryFrom($columSortingString)?->toColumnSortingType();

		$orderSortingString = Configs::getValueAsString('sorting_photos_order');
		$orderSorting = OrderSortingType::tryFrom($orderSortingString);

		return new self(
			$columnSorting ?? ColumnSortingType::CREATED_AT,
			$orderSorting ?? OrderSortingType::DESC
		);
	}
}
