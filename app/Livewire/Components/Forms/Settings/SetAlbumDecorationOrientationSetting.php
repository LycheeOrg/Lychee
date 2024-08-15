<?php

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\AlbumDecorationOrientation;
use App\Legacy\EnumLocalization;
use App\Livewire\Components\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the drop down menu for the orientation of the album decorations.
 */
class SetAlbumDecorationOrientationSetting extends BaseConfigDropDown
{
	/**
	 * Options available.
	 *
	 * @return array<string,string>
	 */
	public function getOptionsProperty(): array
	{
		return EnumLocalization::of(AlbumDecorationOrientation::class);
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