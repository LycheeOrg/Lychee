<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Drop down menu for the language used by Lychee.
 */
class SetLangSetting extends BaseConfigDropDown
{
	/**
	 * Give the list of available languages.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		return config('app.supported_locale');
	}

	/**
	 * Set up the texts.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = __('lychee.LANG_TEXT');
		// We do not use Lang::get_code() because we want to be able to modify it.
		// We are interested in the setting itself.
		$this->config = Configs::where('key', '=', 'lang')->firstOrFail();
	}
}