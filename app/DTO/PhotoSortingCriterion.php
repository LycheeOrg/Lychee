<?php

namespace App\DTO;

use App\Models\Configs;

class PhotoSortingCriterion extends BaseSortingCriterion
{
	public const COLUMN_TAKEN_AT = 'taken_at';
	public const COLUMN_IS_STARRED = 'is_starred';
	public const COLUMN_TYPE = 'type';

	public const COLUMNS = [
		BaseSortingCriterion::COLUMN_CREATED_AT,
		BaseSortingCriterion::COLUMN_TITLE,
		BaseSortingCriterion::COLUMN_DESCRIPTION,
		BaseSortingCriterion::COLUMN_IS_PUBLIC,
		self::COLUMN_TAKEN_AT,
		self::COLUMN_IS_STARRED,
		self::COLUMN_TYPE,
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
			Configs::getValueAsString('sorting_photos_col'),
			Configs::getValueAsString('sorting_photos_order')
		);
	}
}
