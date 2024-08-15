<?php

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\PhotoLayoutType;
use App\Legacy\EnumLocalization;
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
	 * @return array<string,string>
	 */
	public function getOptionsProperty(): array
	{
		return EnumLocalization::of(PhotoLayoutType::class);
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