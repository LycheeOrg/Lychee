<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\ImageOverlayType;
use App\Livewire\Components\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Defines the default overlay when accessing a picture.
 */
class SetPhotoOverlaySetting extends BaseConfigDropDown
{
	/**
	 * Default overlay options.
	 *
	 * @return array<string,string>
	 */
	public function getOptionsProperty(): array
	{
		return ImageOverlayType::localized();
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