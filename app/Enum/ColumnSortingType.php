<?php

namespace App\Enum;

/**
 * Enum ColumnSortingType.
 *
 * All the sorting possibiliies. Do note that this does not apply a limitation to tables.
 */
enum ColumnSortingType: string
{
	case OWNER_ID = 'owner_id';
	case CREATED_AT = 'created_at';
	case TITLE = 'title';
	case DESCRIPTION = 'description';
	case IS_PUBLIC = 'is_public';

	// from albums
	case MIN_TAKEN_AT = 'min_taken_at';
	case MAX_TAKEN_AT = 'max_taken_at';

	// from photos
	case TAKEN_AT = 'taken_at';
	case IS_STARRED = 'is_starred';
	case TYPE = 'type';
}
