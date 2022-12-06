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
		$columSortingString = Configs::getValueAsString('sorting_albums_col');
		$columnSorting = ColumnSortingAlbumType::tryFrom($columSortingString)?->toColumnSortingType();

		$orderSortingString = Configs::getValueAsString('sorting_albums_order');
		$orderSorting = OrderSortingType::tryFrom($orderSortingString);

		return new self(
			$columnSorting ?? ColumnSortingType::CREATED_AT,
			$orderSorting ?? OrderSortingType::ASC
		);
	}
}
