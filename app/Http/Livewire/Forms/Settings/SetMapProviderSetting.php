<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
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
			'Wikimedia' => Lang::get('MAP_PROVIDER_WIKIMEDIA'),
			'OpenStreetMap.org' => Lang::get('MAP_PROVIDER_OSM_ORG'),
			'OpenStreetMap.de' => Lang::get('MAP_PROVIDER_OSM_DE'),
			'OpenStreetMap.fr' => Lang::get('MAP_PROVIDER_OSM_FR'),
			'RRZE' => Lang::get('MAP_PROVIDER_RRZE'),
		];
	}

	/**
	 * Mount the config.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = Lang::get('MAP_PROVIDER');
		$this->config = Configs::where('key', '=', 'map_provider')->firstOrFail();
	}
}