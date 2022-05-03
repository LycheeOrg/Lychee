<?php

namespace App\DTO;

use App\Models\Configs;

class AlbumSortingCriterion extends SortingCriterion
{
	public const COLUMN_MIN_TAKEN_AT = 'min_taken_at';
	public const COLUMN_MAX_TAKEN_AT = 'max_taken_at';

	public const COLUMNS = [
		SortingCriterion::COLUMN_CREATED_AT,
		SortingCriterion::COLUMN_TITLE,
		SortingCriterion::COLUMN_DESCRIPTION,
		SortingCriterion::COLUMN_IS_PUBLIC,
		self::COLUMN_MIN_TAKEN_AT,
		self::COLUMN_MAX_TAKEN_AT,
	];

	/**
	 * @return static
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function createDefault(): self
	{
		/* @noinspection PhpUnhandledExceptionInspection */
		return new self(
			Configs::get_value('sorting_albums_col', SortingCriterion::COLUMN_CREATED_AT),
			Configs::get_value('sorting_albums_order', SortingCriterion::ASC)
		);
	}
}