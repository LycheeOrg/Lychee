<?php

declare(strict_types=1);

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

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::CREATED_AT->value => __('lychee.SORT_PHOTO_SELECT_1'),
			self::TAKEN_AT->value => __('lychee.SORT_PHOTO_SELECT_2'),
			self::TITLE->value => __('lychee.SORT_PHOTO_SELECT_3'),
			self::DESCRIPTION->value => __('lychee.SORT_PHOTO_SELECT_4'),
			self::IS_STARRED->value => __('lychee.SORT_PHOTO_SELECT_6'),
			self::TYPE->value => __('lychee.SORT_PHOTO_SELECT_7'),
		];
	}
}
