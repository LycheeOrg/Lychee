<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Settings\GetAllConfigsRequest;
use App\Http\Requests\Settings\SetConfigsRequest;
use App\Http\Resources\Collections\ConfigCollectionResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the config.
 */
class SettingsController extends Controller
{
	public function getAll(GetAllConfigsRequest $request): ConfigCollectionResource
	{
		return new ConfigCollectionResource(Configs::orderBy('cat', 'asc')->get());
	}

	public function setConfigs(SetConfigsRequest $request): int
	{
		$configs = $request->configs();
		$configs->each(function ($config) {
			Configs::query()->where('key', $config->key)->update(['value' => $config->value]);
		});

		Configs::invalidateCache();

		return 0;
	}
}