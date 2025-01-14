<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

	case TAKEN_AT = 'taken_at';
	case IS_STARRED = 'is_starred';
	case TYPE = 'type';

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
