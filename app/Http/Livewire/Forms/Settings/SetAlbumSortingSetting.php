<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Enum\ColumnSortingAlbumType;
use App\Enum\OrderSortingType;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDoubleDropDown;
use App\Models\Configs;
use function Safe\preg_match;

/**
 * Provide the drop down menu for the sorting type and order of Albums.
 */
class SetAlbumSortingSetting extends BaseConfigDoubleDropDown
{
	/**
	 * Set up the texts.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		// We cannot abuse the sprintf in the case of blade templates compared to JS
		// So we do a simple preg_match to retrieve the chunks.
		// Note this assumes that %1$s is before %2$s !
		/** @var string $sort_album_by */
		$sort_album_by = __('lychee.SORT_ALBUM_BY');
		preg_match('/^(.*)%1\$s(.*)%2\$s(.*)$/', $sort_album_by, $matches);
		$this->begin = $matches[1];
		$this->middle = $matches[2];
		$this->end = $matches[3];

		$this->config1 = Configs::where('key', '=', 'sorting_albums_col')->firstOrFail();
		$this->config2 = Configs::where('key', '=', 'sorting_albums_order')->firstOrFail();
	}

	/**
	 * Give the options on the column.
	 *
	 * @return array
	 */
	public function getOptions1Property(): array
	{
		return [
			ColumnSortingAlbumType::CREATED_AT->value => __('lychee.SORT_ALBUM_SELECT_1'),
			ColumnSortingAlbumType::TITLE->value => __('lychee.SORT_ALBUM_SELECT_2'),
			ColumnSortingAlbumType::DESCRIPTION->value => __('lychee.SORT_ALBUM_SELECT_3'),
			ColumnSortingAlbumType::IS_PUBLIC->value => __('lychee.SORT_ALBUM_SELECT_4'),
			ColumnSortingAlbumType::MAX_TAKEN_AT->value => __('lychee.SORT_ALBUM_SELECT_5'),
			ColumnSortingAlbumType::MIN_TAKEN_AT->value => __('lychee.SORT_ALBUM_SELECT_6'),
		];
	}

	/**
	 * Give the options on the ordering.
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