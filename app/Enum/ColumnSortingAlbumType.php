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
 * All the allowed sorting possibilities on Album
 */
enum ColumnSortingAlbumType: string
{
	case OWNER_ID = 'owner_id';
	case CREATED_AT = 'created_at';
	case TITLE = 'title';
	case DESCRIPTION = 'description';

	// We sort those at the database level.
	case TITLE_STRICT = 'title_strict';
	case DESCRIPTION_STRICT = 'description_strict';

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

	public function toColumn(): string
	{
		return match ($this) {
			self::TITLE_STRICT => 'title',
			self::DESCRIPTION_STRICT => 'description',
			default => $this->value,
		};
	}
}
