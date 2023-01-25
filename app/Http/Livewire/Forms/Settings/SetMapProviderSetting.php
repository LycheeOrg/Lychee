<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Http\Livewire\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;

/**
 * Provides a drop down menu to chose the map provider.
 */
class SetMapProviderSetting extends BaseConfigDropDown
{
	/**
	 * Options for the provider.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		return [
			'Wikimedia' => __('lychee.MAP_PROVIDER_WIKIMEDIA'),
			'OpenStreetMap.org' => __('lychee.MAP_PROVIDER_OSM_ORG'),
			'OpenStreetMap.de' => __('lychee.MAP_PROVIDER_OSM_DE'),
			'OpenStreetMap.fr' => __('lychee.MAP_PROVIDER_OSM_FR'),
			'RRZE' => __('lychee.MAP_PROVIDER_RRZE'),
		];
	}

	/**
	 * Mount the config.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = __('lychee.MAP_PROVIDER');
		$this->config = Configs::where('key', '=', 'map_provider')->firstOrFail();
	}
}