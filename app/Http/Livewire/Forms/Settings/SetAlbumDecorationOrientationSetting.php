<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the drop down menu for the orientation of the album decorations.
 */
class SetAlbumDecorationOrientationSetting extends BaseConfigDropDown
{
	/**
	 * Options available.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		return [
			'row' => __('lychee.ALBUM_DECORATION_ORIENTATION_ROW'),
			'row_reverse' => __('lychee.ALBUM_DECORATION_ORIENTATION_ROW_REVERSE'),
			'column' => __('lychee.ALBUM_DECORATION_ORIENTATION_COLUMN'),
			'column_reverse' => __('lychee.ALBUM_DECORATION_ORIENTATION_COLUMN_REVERSE'),
		];
	}

	/**
	 * Set the required strings.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->description = __('lychee.ALBUM_DECORATION_ORIENTATION');
		$this->config = Configs::where('key', '=', 'album_decoration_orientation')->firstOrFail();
	}
}