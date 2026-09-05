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

	// from albums
	case MIN_TAKEN_AT = 'min_taken_at';
	case MAX_TAKEN_AT = 'max_taken_at';

	// from photos
	case TAKEN_AT = 'taken_at';
	case IS_HIGHLIGHTED = 'is_highlighted';
	case TYPE = 'type';
	case RATING_AVG = 'rating_avg';

	/**
	 * Check if this column requires special raw SQL ordering.
	 * Used for columns that need COALESCE or other SQL functions.
	 */
	public function requiresRawOrdering(): bool
	{
		return $this === self::RATING_AVG || $this === self::TITLE;
	}

	/**
	 * Get the raw SQL ordering expression for this column, including the
	 * sort direction for every part of the expression.
	 * Only applicable when requiresRawOrdering() returns true.
	 *
	 * @param string           $prefix    Optional table prefix (e.g., 'photos.')
	 * @param OrderSortingType $direction the order direction
	 */
	/**
	 * `min_taken_at`/`max_taken_at` are precomputed only on the `albums`
	 * table (real, photo-containing root/folder albums) — `TagAlbum` and
	 * `PersonAlbum` flat category listings carry no such column, so
	 * ordering by either against those tables fails on every SQL backend
	 * except SQLite (which silently tolerates an unresolvable ORDER BY
	 * identifier instead of erroring). Callers building a query against
	 * `tag_albums`/`person_albums` must substitute this fallback for the
	 * globally-configured `sorting_albums_col` before handing it to
	 * {@link \App\Models\Extensions\SortingDecorator}.
	 */
	public function fallbackForCategoryAlbumListing(): self
	{
		return match ($this) {
			self::MIN_TAKEN_AT, self::MAX_TAKEN_AT => self::CREATED_AT,
			default => $this,
		};
	}

	public function getRawOrderExpression(string $prefix, OrderSortingType $direction): string
	{
		return match ($this) {
			// COALESCE pushes NULLs to end by using -1 as sentinel (Q-009-06)
			self::RATING_AVG => 'COALESCE(' . $prefix . 'rating_avg, -1) ' . $direction->value,
			// title_index is NEVER NULL (defaults to 0 for titles without a
			// numeric suffix), so no COALESCE is needed here: a title with
			// no suffix sorts immediately before any digit-suffixed sibling
			// sharing the same base, matching prior natural-sort behaviour
			// (FR-060-05).
			self::TITLE => $prefix . 'title_base ' . $direction->value . ', ' . $prefix . 'title_index ' . $direction->value,
			default => $prefix . $this->value . ' ' . $direction->value,
		};
	}
}
