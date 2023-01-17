<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

class SetLangSetting extends BaseConfigDropDown
{
	public function getOptionsProperty(): array
	{
		return Lang::get_lang_available();
	}

	public function mount()
	{
		$this->description = Lang::get('LANG_TEXT');
		$this->config = Configs::where('key', '=', 'lang')->firstOrFail();
	}
}