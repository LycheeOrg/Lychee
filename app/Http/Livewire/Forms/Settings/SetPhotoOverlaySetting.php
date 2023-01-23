<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the default overlay when accessing a picture.
 */
class SetPhotoOverlaySetting extends BaseConfigDropDown
{
	/**
	 * Default overlay options.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		return [
			'exif' => Lang::get('OVERLAY_EXIF'),
			'desc' => Lang::get('OVERLAY_DESCRIPTION'),
			'date' => Lang::get('OVERLAY_DATE'),
			'none' => Lang::get('OVERLAY_NONE'),
		];
	}

	/**
	 * Set up the drop down menu.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->description = Lang::get('OVERLAY_TYPE');
		$this->config = Configs::where('key', '=', 'image_overlay_type')->firstOrFail();
	}
}