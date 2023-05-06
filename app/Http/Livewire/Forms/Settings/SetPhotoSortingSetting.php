<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDoubleDropDown;
use App\Models\Configs;
use function Safe\preg_match;

/**
 * Set drop down for default ordering in albums.
 */
class SetPhotoSortingSetting extends BaseConfigDoubleDropDown
{
	/**
	 * Set the strings for the menu.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		// We cannot abuse the sprintf in the case of blade templates compared to JS
		// So we do a simple preg_match to retrieve the chunks.
		// Note this assumes that %1$s is before %2$s !
		/** @var string $sort_photo_by */
		$sort_photo_by = __('lychee.SORT_PHOTO_BY');
		preg_match('/^(.*)%1\$s(.*)%2\$s(.*)$/', $sort_photo_by, $matches);
		$this->begin = $matches[1];
		$this->middle = $matches[2];
		$this->end = $matches[3];

		$this->config1 = Configs::where('key', '=', 'sorting_photos_col')->firstOrFail();
		$this->config2 = Configs::where('key', '=', 'sorting_photos_order')->firstOrFail();
	}

	/**
	 * Give the columns options.
	 *
	 * @return array
	 */
	public function getOptions1Property(): array
	{
		return [
			ColumnSortingPhotoType::CREATED_AT->value => __('lychee.SORT_PHOTO_SELECT_1'),
			ColumnSortingPhotoType::TAKEN_AT->value => __('lychee.SORT_PHOTO_SELECT_2'),
			ColumnSortingPhotoType::TITLE->value => __('lychee.SORT_PHOTO_SELECT_3'),
			ColumnSortingPhotoType::DESCRIPTION->value => __('lychee.SORT_PHOTO_SELECT_4'),
			ColumnSortingPhotoType::IS_PUBLIC->value => __('lychee.SORT_PHOTO_SELECT_5'),
			ColumnSortingPhotoType::IS_STARRED->value => __('lychee.SORT_PHOTO_SELECT_6'),
			ColumnSortingPhotoType::TYPE->value => __('lychee.SORT_PHOTO_SELECT_7'),
		];
	}

	/**
	 * Ordering ascending or descending.
	 *
	 * @return array
	 */
	public function getOptions2Property(): array
	{
		return [
			OrderSortingType::ASC->value => __('lychee.SORT_ASCENDING'),
			OrderSortingType::DESC->value => __('lychee.SORT_DESCENDING'),
		];
	}
}