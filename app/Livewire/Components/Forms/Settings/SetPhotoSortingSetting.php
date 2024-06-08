<?php

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Livewire\Components\Forms\Settings\Base\BaseConfigDoubleDropDown;
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
	 * @return array<string,string>
	 */
	public function getOptions1Property(): array
	{
		return ColumnSortingPhotoType::localized();
	}

	/**
	 * Ordering ascending or descending.
	 *
	 * @return array<string,string>
	 */
	public function getOptions2Property(): array
	{
		return OrderSortingType::localized();
	}
}