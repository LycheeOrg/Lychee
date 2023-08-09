<?php

namespace App\Livewire\Components\Forms\Settings;

use App\Livewire\Components\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the drop down menu for the layout used by the gallery.
 */
class SetLayoutSetting extends BaseConfigDropDown
{
	/**
	 * Provides the different options.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		// ! Here we MUST do 1 0 2 3 order otherwise php makes a stupid conversion to int.
		return [
			'1' => __('lychee.LAYOUT_JUSTIFIED'), // \App\Enum\Livewire\AlbumMode::SQUARE
			'0' => __('lychee.LAYOUT_SQUARES'), // \App\Enum\Livewire\AlbumMode::FLKR
			'2' => __('lychee.LAYOUT_MASONRY'), // \App\Enum\Livewire\AlbumMode::MASONRY
			'3' => __('lychee.LAYOUT_GRID'), // \App\Enum\Livewire\AlbumMode::GRID
		];
	}

	/**
	 * Mount the texts.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = __('lychee.LAYOUT_TYPE');
		$this->config = Configs::where('key', '=', 'layout')->firstOrFail();
	}
}