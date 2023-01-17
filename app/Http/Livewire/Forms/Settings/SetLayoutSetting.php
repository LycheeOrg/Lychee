<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

class SetLayoutSetting extends BaseConfigDropDown
{
	public function getOptionsProperty(): array
	{
		return [
			'0' => Lang::get('LAYOUT_SQUARES'), // \App\Enum\Livewire\AlbumMode::FLKR
			'1' => Lang::get('LAYOUT_JUSTIFIED'), // \App\Enum\Livewire\AlbumMode::SQUARE
			// ! FIX ME
			'2' => Lang::get('LAYOUT_UNJUSTIFIED'), // \App\Enum\Livewire\AlbumMode::MASONRY
		];
	}

	public function mount()
	{
		$this->description = Lang::get('LAYOUT_TYPE');
		$this->config = Configs::where('key', '=', 'layout')->firstOrFail();
	}
}