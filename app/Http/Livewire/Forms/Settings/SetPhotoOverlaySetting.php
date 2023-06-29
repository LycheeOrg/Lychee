<?php

namespace App\Http\Livewire\Forms\Settings;

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
			'exif' => __('lychee.OVERLAY_EXIF'),
			'desc' => __('lychee.OVERLAY_DESCRIPTION'),
			'date' => __('lychee.OVERLAY_DATE'),
			'none' => __('lychee.OVERLAY_NONE'),
		];
	}

	/**
	 * Set up the drop down menu.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->description = __('lychee.OVERLAY_TYPE');
		$this->config = Configs::where('key', '=', 'image_overlay_type')->firstOrFail();
	}
}