<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

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

	// We sort those at the database level.
	case TITLE_STRICT = 'title_strict';
	case DESCRIPTION_STRICT = 'description_strict';

	// from albums
	case MIN_TAKEN_AT = 'min_taken_at';
	case MAX_TAKEN_AT = 'max_taken_at';

	// from photos
	case TAKEN_AT = 'taken_at';
	case IS_HIGHLIGHTED = 'is_highlighted';
	case TYPE = 'type';
	case RATING_AVG = 'rating_avg';

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

	/**
	 * Check if this column requires special raw SQL ordering.
	 * Used for columns that need COALESCE or other SQL functions.
	 */
	public function requiresRawOrdering(): bool
	{
		return $this === self::RATING_AVG;
	}

	/**
	 * Get the raw SQL ordering expression for this column.
	 * Only applicable when requiresRawOrdering() returns true.
	 *
	 * @param string $prefix Optional table prefix (e.g., 'photos.')
	 */
	public function getRawOrderExpression(string $prefix = ''): string
	{
		return match ($this) {
			// COALESCE pushes NULLs to end by using -1 as sentinel (Q-009-06)
			self::RATING_AVG => 'COALESCE(' . $prefix . 'rating_avg, -1)',
			default => $prefix . $this->toColumn(),
		};
	}
}
