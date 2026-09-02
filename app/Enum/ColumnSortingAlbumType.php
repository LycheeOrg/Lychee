<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum ColumnSortingAlbumType.
 *
 * All the allowed sorting possibilities on Album.
 *
 * `OWNER_ID` was removed by Feature 062 (FR-062-08, G5): a UI dropdown
 * narrowing already shipped in Feature 060 quietly removed it from the
 * *offered* choices (`configs.type_range` for `sorting_albums_col`) without
 * ever removing it from this enum. `ColumnSortingType::OWNER_ID` (the
 * broader, internal enum) is untouched — it stays available for internal,
 * non-configurable ordering (root's `shared`-scope owner grouping).
 */
enum ColumnSortingAlbumType: string
{
	case CREATED_AT = 'created_at';

	case TITLE = 'title';

	case MIN_TAKEN_AT = 'min_taken_at';
	case MAX_TAKEN_AT = 'max_taken_at';

	/**
	 * Convert into Column Sorting type.
	 *
	 * @return ColumnSortingType
	 */
	public function toColumnSortingType(): ColumnSortingType
	{
		return ColumnSortingType::from($this->value);
	}
}
