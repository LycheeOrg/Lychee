<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

class SetAlbumDecorationOrientationSetting extends BaseConfigDropDown
{
	public function getOptionsProperty(): array
	{
		return [
			'row' => Lang::get('ALBUM_DECORATION_ORIENTATION_ROW'),
			'row_reverse' => Lang::get('ALBUM_DECORATION_ORIENTATION_ROW_REVERSE'),
			'column' => Lang::get('ALBUM_DECORATION_ORIENTATION_COLUMN'),
			'column_reverse' => Lang::get('ALBUM_DECORATION_ORIENTATION_COLUMN_REVERSE'),
		];
	}

	public function mount()
	{
		$this->description = Lang::get('ALBUM_DECORATION_ORIENTATION');
		$this->config = Configs::where('key', '=', 'album_decoration_orientation')->firstOrFail();
	}
}