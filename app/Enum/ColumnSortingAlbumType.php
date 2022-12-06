<?php

namespace App\Enum;

/**
 * Enum AlbumColumnSortingType.
 */
enum ColumnSortingAlbumType: string
{
	case OWNER_ID = 'owner_id';
	case CREATED_AT = 'created_at';
	case TITLE = 'title';
	case DESCRIPTION = 'description';
	case IS_PUBLIC = 'is_public';

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
