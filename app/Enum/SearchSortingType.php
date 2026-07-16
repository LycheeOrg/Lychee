<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum SearchSortingType.
 *
 * The closed set of columns the search page lets the user sort results by.
 */
enum SearchSortingType: string
{
	case TITLE = 'title';
	case CREATED_AT = 'created_at';
	case TAKEN_AT = 'taken_at';

	/**
	 * Convert into the column used to sort photos.
	 */
	public function toPhotoColumn(): ColumnSortingPhotoType
	{
		return match ($this) {
			// Sorted at the database level for true lexicographic (byte) order,
			// unlike the (PHP-level, natural-sort) plain TITLE column.
			self::TITLE => ColumnSortingPhotoType::TITLE_STRICT,
			self::CREATED_AT => ColumnSortingPhotoType::CREATED_AT,
			self::TAKEN_AT => ColumnSortingPhotoType::TAKEN_AT,
		};
	}

	/**
	 * Convert into the column used to sort albums.
	 *
	 * Albums have no single `taken_at`: they aggregate the photos they
	 * contain, so the ordering direction picks which bound to sort by
	 * (descending -> most recent photo first -> `max_taken_at`; ascending ->
	 * oldest photo first -> `min_taken_at`).
	 */
	public function toAlbumColumn(OrderSortingType $order): ColumnSortingAlbumType
	{
		return match ($this) {
			self::TITLE => ColumnSortingAlbumType::TITLE_STRICT,
			self::CREATED_AT => ColumnSortingAlbumType::CREATED_AT,
			self::TAKEN_AT => $order === OrderSortingType::DESC
				? ColumnSortingAlbumType::MAX_TAKEN_AT
				: ColumnSortingAlbumType::MIN_TAKEN_AT,
		};
	}
}
