<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\AlbumDecorationType;
use App\Livewire\Components\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the drop down menu for the decoration of albums.
 */
class SetAlbumDecorationSetting extends BaseConfigDropDown
{
	/**
	 * Specify the options available.
	 *
	 * @return array<string,string>
	 */
	public function getOptionsProperty(): array
	{
		return AlbumDecorationType::localized();
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