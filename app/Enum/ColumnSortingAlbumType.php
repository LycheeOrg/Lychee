<?php

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

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function toTranslation(): array
	{
		return [
			self::CREATED_AT->value => __('lychee.SORT_ALBUM_SELECT_1'),
			self::TITLE->value => __('lychee.SORT_ALBUM_SELECT_2'),
			self::DESCRIPTION->value => __('lychee.SORT_ALBUM_SELECT_3'),
			self::MIN_TAKEN_AT->value => __('lychee.SORT_ALBUM_SELECT_5'),
			self::MAX_TAKEN_AT->value => __('lychee.SORT_ALBUM_SELECT_6'),
		];
	}
}
