<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum ColumnSortingPhotoType.
 *
 * All the allowed sorting possibilities on Photos.
 */
enum ColumnSortingPhotoType: string
{
	use DecorateBackedEnum;

	case OWNER_ID = 'owner_id';
	case CREATED_AT = 'created_at';
	case TITLE = 'title';
	case DESCRIPTION = 'description';

	// We sort those at the database level.
	case TITLE_STRICT = 'title_strict';
	case DESCRIPTION_STRICT = 'description_strict';

	case TAKEN_AT = 'taken_at';
	case IS_HIGHLIGHTED = 'is_highlighted';
	case TYPE = 'type';
	case RATING_AVG = 'rating_avg';

	/**
	 * Convert into Column Sorting type.
	 *
	 * @return ColumnSortingType
	 */
	public function toColumnSortingType(): ColumnSortingType
	{
		return ColumnSortingType::from($this->value);
	}

	/**
	 * Convert into actual column name.
	 */
	public function toColumn(): string
	{
		return match ($this) {
			self::TITLE_STRICT => 'title',
			self::DESCRIPTION_STRICT => 'description',
			default => $this->value,
		};
	}
}
