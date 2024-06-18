<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\LicenseType;
use App\Livewire\Components\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Drop Down menu for the default license.
 */
class SetLicenseDefaultSetting extends BaseConfigDropDown
{
	/**
	 * We have to use this mapping to provide easilly readable license type.
	 *
	 * @return array<string,string>
	 */
	public function getOptionsProperty(): array
	{
		return LicenseType::localized();
	}

	/**
	 * Set up the drop down menu.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->description = __('lychee.DEFAULT_LICENSE');
		$this->config = Configs::where('key', '=', 'default_license')->firstOrFail();
	}
}