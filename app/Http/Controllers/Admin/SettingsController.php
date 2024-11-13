<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientFilesystemPermissions;
use App\Http\Requests\Settings\GetAllConfigsRequest;
use App\Http\Requests\Settings\SetConfigsRequest;
use App\Http\Requests\Settings\SetCSSSettingRequest;
use App\Http\Requests\Settings\SetJSSettingRequest;
use App\Http\Resources\Collections\ConfigCollectionResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for the config.
 */
class SettingsController extends Controller
{
	public function getAll(GetAllConfigsRequest $request): ConfigCollectionResource
	{
		$editable_configs = Configs::query()
			->when(config('features.hide-lychee-SE', false) === true, fn ($q) => $q->where('cat', '!=', 'lychee SE'))
			->when(!$request->is_se() && !Configs::getValueAsBool('enable_se_preview'), fn ($q) => $q->where('level', '=', 0))
			->orderBy('cat', 'asc')->get();

		return new ConfigCollectionResource($editable_configs);
	}

	public function setConfigs(SetConfigsRequest $request): ConfigCollectionResource
	{
		$configs = $request->configs();
		$configs->each(function ($config) {
			Configs::query()->where('key', $config->key)->update(['value' => $config->value ?? '']);
		});

		Configs::invalidateCache();

		return new ConfigCollectionResource(Configs::orderBy('cat', 'asc')->get());
	}

	/**
	 * Give the list of available languages.
	 *
	 * @return string[]
	 */
	public function getLanguages(GetAllConfigsRequest $request): array
	{
		// @phpstan-ignore-next-line
		return collect(config('app.supported_locale'))->filter(function ($value, $key) {
			return !str_contains($value, 'json');
		})->values()->toArray();
	}

	/**
	 * Takes the js input text and puts it into `dist/custom.js`.
	 * This allows admins to actually execute custom js code on their
	 * Lychee-Laravel installation.
	 *
	 * @param SetJSSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setJS(SetJSSettingRequest $request): void
	{
		$js = $request->getJs();
		if (Storage::disk('dist')->put('custom.js', $js) === false) {
			if (Storage::disk('dist')->get('custom.js') !== $js) {
				throw new InsufficientFilesystemPermissions('Could not save JS');
			}
		}
	}

	/**
	 * Takes the css input text and put it into `dist/user.css`.
	 * This allows admins to actually personalize the look of their
	 * installation.
	 *
	 * @param SetCSSSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setCSS(SetCSSSettingRequest $request): void
	{
		$css = $request->getCss();
		if (Storage::disk('dist')->put('user.css', $css) === false) {
			if (Storage::disk('dist')->get('user.css') !== $css) {
				throw new InsufficientFilesystemPermissions('Could not save CSS');
			}
		}
	}
}