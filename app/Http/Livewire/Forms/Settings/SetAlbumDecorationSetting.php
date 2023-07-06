<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the drop down menu for the decoration of albums.
 */
class SetAlbumDecorationSetting extends BaseConfigDropDown
{
	/**
	 * Specify the options available.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		return [
			'none' => __('lychee.ALBUM_DECORATION_NONE'),
			'layers' => __('lychee.ALBUM_DECORATION_ORIGINAL'),
			'album' => __('lychee.ALBUM_DECORATION_ALBUM'),
			'photo' => __('lychee.ALBUM_DECORATION_PHOTO'),
			'all' => __('lychee.ALBUM_DECORATION_ALL'),
		];
	}

	/**
	 * Set up the translations.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = __('lychee.ALBUM_DECORATION');
		$this->config = Configs::where('key', '=', 'album_decoration')->firstOrFail();
	}
}