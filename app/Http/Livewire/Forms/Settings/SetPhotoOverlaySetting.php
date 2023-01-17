<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

class SetPhotoOverlaySetting extends BaseConfigDropDown
{
	public function getOptionsProperty(): array
	{
		return [
			"exif" => Lang::get('OVERLAY_EXIF'),
			"desc" => Lang::get('OVERLAY_DESCRIPTION'),
			"date" => Lang::get('OVERLAY_DATE'),
			"none" => Lang::get('OVERLAY_NONE'),
		];
	}

	public function mount()
	{
		$this->description = Lang::get('OVERLAY_TYPE');
		$this->config = Configs::where('key', '=', 'image_overlay_type')->firstOrFail();
	}
}