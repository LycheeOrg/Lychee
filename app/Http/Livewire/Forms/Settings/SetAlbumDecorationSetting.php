<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
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
			'none' => Lang::get('ALBUM_DECORATION_NONE'),
			'layers' => Lang::get('ALBUM_DECORATION_ORIGINAL'),
			'album' => Lang::get('ALBUM_DECORATION_ALBUM'),
			'photo' => Lang::get('ALBUM_DECORATION_PHOTO'),
			'all' => Lang::get('ALBUM_DECORATION_ALL'),
		];
	}

	/**
	 * Set up the translations.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = Lang::get('ALBUM_DECORATION');
		$this->config = Configs::where('key', '=', 'album_decoration')->firstOrFail();
	}
}