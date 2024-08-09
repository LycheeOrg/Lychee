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

	public function setConfigs(SetConfigsRequest $request): ConfigCollectionResource
	{
		$configs = $request->configs();
		$configs->each(function ($config) {
			Configs::query()->where('key', $config->key)->update(['value' => $config->value]);
		});

		Configs::invalidateCache();

		return new ConfigCollectionResource(Configs::orderBy('cat', 'asc')->get());
	}

	/**
	 * Give the list of available languages.
	 *
	 * @return string[]
	 */
	public function getLanguages(): array
	{
		// @phpstan-ignore-next-line
		return collect(config('app.supported_locale'))->filter(function ($value, $key) {
			return !str_contains($value, 'json');
		})->values()->toArray();
	}
}