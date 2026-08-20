<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Diagnostics\Pipes\Infos\DockerVersionInfo;
use App\Constants\FileSystem;
use App\Enum\CacheTag;
use App\Events\AlbumListingCacheFlushRequested;
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
	 * Config keys whose change affects an album listing with no per-album row
	 * to hook (global sort/pagination/feature-toggle config) — closes over
	 * every cached album listing via one coarse, instance-wide flush.
	 */
	public const ALBUM_LISTING_COARSE_FLUSH_CONFIGS = [
		'sorting_albums_col',
		'sorting_albums_order',
		'sorting_pinned_albums_col',
		'sorting_pinned_albums_order',
		'deduplicate_pinned_albums',
		'ai_vision_face_enabled',
		'albums_per_page',
	];

	public const V8_CONFIGS = [
		'site_logo',
		'primary_color',
		'enable_design_system',
		'secondary_color',
		'success_color',
		'warning_color',
		'error_color',
		'info_color',
		'neutral_color',
		'landing_logo',
		'landing_header_logo',
		'breadcrumb_enabled',
		'rounded_corners_enabled',
		'album_border_enabled',
		'selection_border_enabled',
		'selection_overlay_enabled',
		'photo_ken_burns_on_hover_enabled',
		'photo_ken_burns_on_hover_scale',
		'photo_ken_burns_on_hover_duration',
		'photo_share_card_enabled',
		'flags_enabled',
		'photo_flags_enabled',
		'cover_id_flag_enabled',
		'header_id_flag_enabled',
		'highlighted_flag_enabled',
		'validated_flag_enabled',
		'smart_album_flags_enabled',
		'album_flags_enabled',
		'public_hidden_flag_enabled',
		'public_visible_flag_enabled',
		'password_flag_enabled',
		'sensitive_flag_enabled',
		'expert_album_settings',
		'sm_pinterest_url',
		'sm_deviantart_url',
		'sm_tumblr_url',
		'sm_500px_url',
		'sm_pixelfeed_url',
		'sm_discord_url',
		'sm_reddit_url',
		'landing_layout',
		'landing_intro_screen_enabled',
		'landing_backdrop_opacity',
		'landing_hero_text_position',
		'landing_hero_text_color',
		'landing_hero_text_opacity',
		'landing_animation_preset',
		'landing_about_enabled',
		'landing_about_text',
		'landing_featured_items_enabled',
		'landing_featured_items_mode',
		'landing_featured_items_count',
		'landing_cta_text',
		'landing_cta_position',
		'landing_cta_shift_type',
		'landing_cta_shift_x',
		'landing_cta_shift_x_direction',
		'landing_cta_shift_y',
		'landing_cta_shift_y_direction',
		'landing_meridian_explore_offset',
		'landing_meridian_contact_offset',
		'landing_meridian_explore_line_position',
		'landing_meridian_contact_line_position',
		'landing_login_position',
	];

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
			'configs' => fn ($query) => $query
				->when(config('features.hide-lychee-SE', false) === true, fn ($q) => $q->where('cat', '!=', 'lychee SE'))
				->when(
					config('features.enable-caching') === false,
					fn ($q) => $q->where(fn ($q2) => $q2->where('cat', '!=', 'Mod Cache'))
				)
				->when($docker_info->isDocker(), fn ($q) => $q->where('not_on_docker', '!=', true))
				->when(!$request->verify()->is_supporter() && !$request->configs()->getValueAsBool('enable_se_preview'), fn ($q) => $q->where('level', '=', 0))
				->when(!$request->verify()->is_pro(), fn ($q) => $q->where('level', '<', 2))
				->when(config('features.webshop') === false, fn ($q) => $q->where('key', 'NOT LIKE', 'webshop_%'))
				->when(config('features.ai-vision') === false || config('features.v8') === false, fn ($q) => $q->where('key', 'NOT LIKE', 'ai_vision_%'))
				->when(config('features.v8') === false, fn ($q) => $q->whereNotIn('key', self::V8_CONFIGS)),
		])->orderBy('order', 'asc')->get();

		return ConfigCategoryResource::collect($editable_configs->filter(fn ($cat) => $cat->configs->isNotEmpty())->values());
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
		$configs = $request->editable_configs();
		$configs->each(function ($config): void {
			Configs::query()->where('key', $config->key)->update(['value' => $config->value ?? '']);
		});

		AlbumListingCacheFlushRequested::dispatchIf($configs->pluck('key')->intersect(self::ALBUM_LISTING_COARSE_FLUSH_CONFIGS)->isNotEmpty());

		$request->configs()->invalidateCache();
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
