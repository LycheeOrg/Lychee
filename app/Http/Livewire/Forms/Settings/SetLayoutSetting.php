<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
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
		return [
			'0' => __('lychee.LAYOUT_SQUARES'), // \App\Enum\Livewire\AlbumMode::FLKR
			'1' => __('lychee.LAYOUT_JUSTIFIED'), // \App\Enum\Livewire\AlbumMode::SQUARE
			// ! FIX ME
			'2' => __('lychee.LAYOUT_UNJUSTIFIED'), // \App\Enum\Livewire\AlbumMode::MASONRY
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