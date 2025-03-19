<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Diagnostics\Pipes\Infos\DockerVersionInfo;
use App\Constants\FileSystem;
use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Http\Requests\Settings\GetAllConfigsRequest;
use App\Http\Requests\Settings\SetConfigsRequest;
use App\Http\Requests\Settings\SetCSSSettingRequest;
use App\Http\Requests\Settings\SetJSSettingRequest;
use App\Http\Resources\GalleryConfigs\SettingsConfig;
use App\Http\Resources\Models\ConfigCategoryResource;
use App\Models\ConfigCategory;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for the config.
 */
class SettingsController extends Controller
{
	/**
	 * Fetch all the settings available in Lychee.
	 *
	 * @param GetAllConfigsRequest $request
	 * @param DockerVersionInfo    $docker_info
	 * @param GetAllConfigsRequest $request
	 * @param DockerVersionInfo    $docker_info
	 *
	 * @return Collection<int,ConfigCategoryResource>
	 */
	public function getAll(GetAllConfigsRequest $request, DockerVersionInfo $docker_info): Collection
	{
		$editable_configs = ConfigCategory::with([
			'configs' => fn ($query) => $query->when(config('features.hide-lychee-SE', false) === true, fn ($q) => $q->where('cat', '!=', 'lychee SE'))
				->when($docker_info->isDocker(), fn ($q) => $q->where('not_on_docker', '!==', true))
				->when(!$request->is_se() && !Configs::getValueAsBool('enable_se_preview'), fn ($q) => $q->where('level', '=', 0)),
		])->orderBy('order', 'asc')->get();

		return ConfigCategoryResource::collect($editable_configs)->filter(fn ($cat) => $cat->configs->isNotEmpty())->values();
	}

	/**
	 * Set a limited number of configurations with the new values.
	 *
	 * @param SetConfigsRequest $request
	 * @param DockerVersionInfo $docker_info
	 *
	 * @return Collection<int,ConfigCategoryResource>
	 */
	public function setConfigs(SetConfigsRequest $request, DockerVersionInfo $docker_info): Collection
	{
		$configs = $request->configs();
		$configs->each(function ($config): void {
			Configs::query()->where('key', $config->key)->update(['value' => $config->value ?? '']);
		});

		Configs::invalidateCache();
		TaggedRouteCacheUpdated::dispatch(CacheTag::SETTINGS);

		return $this->getAll($request, $docker_info);
	}

	/**
	 * Give the list of available languages.
	 *
	 * @return string[]
	 */
	public function getLanguages(GetAllConfigsRequest $request): array
	{
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
		if (Storage::disk(FileSystem::DIST)->put('custom.js', $js) === false) {
			// @codeCoverageIgnoreStart
			// We do not test this part as this would require to change the access rights of the file
			if (Storage::disk(FileSystem::DIST)->get('custom.js') !== $js) {
				throw new InsufficientFilesystemPermissions('Could not save JS');
			}
			// @codeCoverageIgnoreEnd
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
		if (Storage::disk(FileSystem::DIST)->put('user.css', $css) === false) {
			// @codeCoverageIgnoreStart
			// We do not test this part as this would require to change the access rights of the file
			if (Storage::disk(FileSystem::DIST)->get('user.css') !== $css) {
				throw new InsufficientFilesystemPermissions('Could not save CSS');
			}
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Return the necessary information to configure the settings page.
	 *
	 * @return SettingsConfig
	 */
	public function getConfig(GetAllConfigsRequest $request): SettingsConfig
	{
		return new SettingsConfig();
	}
}
