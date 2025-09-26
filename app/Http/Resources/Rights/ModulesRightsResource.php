<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Image\Watermarker;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use LycheeVerify\Verify;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ModulesRightsResource extends Data
{
	private readonly Verify $verify;
	public bool $is_map_enabled = false;
	public bool $is_mod_frame_enabled = false;
	public bool $is_mod_flow_enabled = false;
	public bool $is_watermarker_enabled = false;
	public bool $is_photo_timeline_enabled = false;
	public bool $is_mod_renamer_enabled = false;
	public bool $is_mod_webshop_enabled = false;

	public function __construct()
	{
		$this->verify = resolve(Verify::class);
		$is_logged_in = Auth::check();

		$this->is_map_enabled = $this->isMapEnabled($is_logged_in);
		$this->is_mod_frame_enabled = $this->isModFrameEnabled();
		$this->is_mod_flow_enabled = $this->isModFlowEnabled($is_logged_in);
		$this->is_watermarker_enabled = $this->isWatermarkerEnabled($is_logged_in);
		$this->is_photo_timeline_enabled = $this->isTimelinePhotosEnabled($is_logged_in);
		$this->is_mod_renamer_enabled = $this->isRenamerEnabled();
		$this->is_mod_webshop_enabled = $this->isWebshopEnabled();
	}

	/**
	 * Check if the map module is enabled and accessible to the current user.
	 *
	 * @param bool $is_logged_in
	 *
	 * @return bool true if the map module is enabled and accessible, false otherwise
	 */
	private function isMapEnabled(bool $is_logged_in): bool
	{
		$has_locations = Photo::whereNotNull('latitude')->whereNotNull('longitude')->exists();
		$map_display = Configs::getValueAsBool('map_display');
		$public_display = $is_logged_in || Configs::getValueAsBool('map_display_public');

		return $has_locations && $map_display && $public_display;
	}

	/**
	 * Check if the frame module is enabled and accessible to the current user.
	 *
	 * @return bool true if the frame module is enabled and accessible, false otherwise
	 */
	private function isModFrameEnabled(): bool
	{
		if (!Configs::getValueAsBool('mod_frame_enabled')) {
			return false;
		}

		$factory = resolve(AlbumFactory::class);
		try {
			$random_album_id = Configs::getValueAsString('random_album_id');
			$random_album_id = ($random_album_id !== '') ? $random_album_id : null;
			$album = $factory->findNullalbleAbstractAlbumOrFail($random_album_id);

			return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $album]);
		} catch (\Throwable) {
			Log::critical('Could not find random album for frame with ID:' . Configs::getValueAsString('random_album_id'));

			return false;
		}
	}

	/**
	 * Check if the flow module is enabled and accessible to the current user.
	 * The flow module provides a dynamic and visually appealing way to browse through photos.
	 *
	 * @param bool $is_logged_in
	 *
	 * @return bool
	 */
	private function isModFlowEnabled(bool $is_logged_in): bool
	{
		if (!Configs::getValueAsBool('flow_enabled')) {
			return false;
		}

		return $is_logged_in || Configs::getValueAsBool('flow_public');
	}

	/**
	 * Check if the timeline photos module is enabled and accessible to the current user.
	 *
	 * @param bool $is_logged_in
	 *
	 * @return bool
	 */
	private function isTimelinePhotosEnabled(bool $is_logged_in): bool
	{
		$timeline_photos_enabled = Configs::getValueAsBool('timeline_photos_enabled');
		$timeline_photos_public = Configs::getValueAsBool('timeline_photos_public');

		return $timeline_photos_enabled && ($is_logged_in || $timeline_photos_public);
	}

	/**
	 * Check if the watermarker module is enabled and accessible to the current user.
	 * The watermarker module allows users to apply watermarks to photos for protection and branding purposes.
	 *
	 * With this function we inform the front-end that the watermarker can be triggered to be applied to photos.
	 *
	 * @return bool true if the watermarker is enabled and accessible, false otherwise
	 */
	private function isWatermarkerEnabled(bool $is_logged_in): bool
	{
		if (!$is_logged_in) {
			return false;
		}

		if (!$this->verify->check()) {
			return false;
		}

		return resolve(Watermarker::class)->can_watermark;
	}

	/**
	 * Check if the renamer module is enabled and accessible to the current user.
	 *
	 * The renamer module allows users to create and manage rules for automatically
	 * renaming photos based on patterns and replacements.
	 *
	 * @return bool true if the renamer is enabled and accessible, false otherwise
	 */
	private function isRenamerEnabled(): bool
	{
		if (!$this->verify->check()) {
			return false;
		}

		if (!Configs::getValueAsBool('renamer_enabled')) {
			return false;
		}

		if (Configs::getValueAsBool('renamer_enforced') && Auth::user()?->may_administrate !== true) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the webshop module is enabled.
	 *
	 * @return bool true if the webshop is enabled, false otherwise
	 */
	private function isWebshopEnabled(): bool
	{
		if (!$this->verify->check()) {
			return false;
		}

		return Configs::getValueAsBool('webshop_enabled');
	}
}
