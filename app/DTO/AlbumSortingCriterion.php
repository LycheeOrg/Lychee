<?php

namespace App\DTO;

use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Configs;

class AlbumSortingCriterion extends SortingCriterion
{
	/**
	 * @return self
	 */
	public static function createDefault(): self
	{
		$columnSorting = Configs::getValueAsEnum('sorting_albums_col', ColumnSortingAlbumType::class);
		$columnSorting = $columnSorting?->toColumnSortingType();

		$orderSorting = Configs::getValueAsEnum('sorting_albums_order', OrderSortingType::class);

		return new self(
			$columnSorting ?? ColumnSortingType::CREATED_AT,
			$orderSorting ?? OrderSortingType::ASC
		);
	}
}
