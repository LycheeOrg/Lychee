<?php

namespace App\DTO;

use App\Models\Configs;

class AlbumSortingCriterion extends BaseSortingCriterion
{
	public const COLUMN_MIN_TAKEN_AT = 'min_taken_at';
	public const COLUMN_MAX_TAKEN_AT = 'max_taken_at';

	public const COLUMNS = [
		BaseSortingCriterion::COLUMN_CREATED_AT,
		BaseSortingCriterion::COLUMN_TITLE,
		BaseSortingCriterion::COLUMN_DESCRIPTION,
		BaseSortingCriterion::COLUMN_IS_PUBLIC,
		self::COLUMN_MIN_TAKEN_AT,
		self::COLUMN_MAX_TAKEN_AT,
	];

	/**
	 * @return self
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function createDefault(): self
	{
		/* @noinspection PhpUnhandledExceptionInspection */
		return new self(
			Configs::getValueAsString('sorting_albums_col'),
			Configs::getValueAsString('sorting_albums_order')
		);
	}
}
