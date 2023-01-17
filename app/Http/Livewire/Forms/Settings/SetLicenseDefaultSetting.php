<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

class SetLicenseDefaultSetting extends BaseConfigDropDown
{
	public function getOptionsProperty(): array
	{
		return [
			'none' => 'None',
			'reserved' => 'All Rights Reserved',
			'CC0' => 'CC0 - Public Domain',
			'CC-BY-1.0' => 'CC Attribution 1.0',
			'CC-BY-2.0' => 'CC Attribution 2.0',
			'CC-BY-2.5' => 'CC Attribution 2.5',
			'CC-BY-3.0' => 'CC Attribution 3.0',
			'CC-BY-4.0' => 'CC Attribution 4.0',
			'CC-BY-ND-1.0' => 'CC Attribution-NoDerivatives 1.0',
			'CC-BY-ND-2.0' => 'CC Attribution-NoDerivatives 2.0',
			'CC-BY-ND-2.5' => 'CC Attribution-NoDerivatives 2.5',
			'CC-BY-ND-3.0' => 'CC Attribution-NoDerivatives 3.0',
			'CC-BY-ND-4.0' => 'CC Attribution-NoDerivatives 4.0',
			'CC-BY-SA-1.0' => 'CC Attribution-ShareAlike 1.0',
			'CC-BY-SA-2.0' => 'CC Attribution-ShareAlike 2.0',
			'CC-BY-SA-2.5' => 'CC Attribution-ShareAlike 2.5',
			'CC-BY-SA-3.0' => 'CC Attribution-ShareAlike 3.0',
			'CC-BY-SA-4.0' => 'CC Attribution-ShareAlike 4.0',
			'CC-BY-NC-1.0' => 'CC Attribution-NonCommercial 1.0',
			'CC-BY-NC-2.0' => 'CC Attribution-NonCommercial 2.0',
			'CC-BY-NC-2.5' => 'CC Attribution-NonCommercial 2.5',
			'CC-BY-NC-3.0' => 'CC Attribution-NonCommercial 3.0',
			'CC-BY-NC-4.0' => 'CC Attribution-NonCommercial 4.0',
			'CC-BY-NC-ND-1.0' => 'CC Attribution-NonCommercial-NoDerivatives 1.0',
			'CC-BY-NC-ND-2.0' => 'CC Attribution-NonCommercial-NoDerivatives 2.0',
			'CC-BY-NC-ND-2.5' => 'CC Attribution-NonCommercial-NoDerivatives 2.5',
			'CC-BY-NC-ND-3.0' => 'CC Attribution-NonCommercial-NoDerivatives 3.0',
			'CC-BY-NC-ND-4.0' => 'CC Attribution-NonCommercial-NoDerivatives 4.0',
			'CC-BY-NC-SA-1.0' => 'CC Attribution-NonCommercial-ShareAlike 1.0',
			'CC-BY-NC-SA-2.0' => 'CC Attribution-NonCommercial-ShareAlike 2.0',
			'CC-BY-NC-SA-2.5' => 'CC Attribution-NonCommercial-ShareAlike 2.5',
			'CC-BY-NC-SA-3.0' => 'CC Attribution-NonCommercial-ShareAlike 3.0',
			'CC-BY-NC-SA-4.0' => 'CC Attribution-NonCommercial-ShareAlike 4.0',
		];
	}

	public function mount()
	{
		$this->description = Lang::get('DEFAULT_LICENSE');
		$this->config = Configs::where('key', '=', 'default_license')->firstOrFail();
	}
}