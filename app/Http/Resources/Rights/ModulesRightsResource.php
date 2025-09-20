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
	private Verify $verify;

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
		$count_locations = Photo::whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
		$map_display = Configs::getValueAsBool('map_display');
		$public_display = $is_logged_in || Configs::getValueAsBool('map_display_public');

		$this->is_map_enabled = $count_locations && $map_display && $public_display;
		$this->is_mod_frame_enabled = $this->checkModFrameEnabled();
		$this->is_mod_flow_enabled = Configs::getValueAsBool('flow_enabled') && (Auth::check() || Configs::getValueAsBool('flow_public'));

		$this->is_watermarker_enabled = resolve(Watermarker::class)->can_watermark && Auth::check() && $this->verify->check();

		$timeline_photos_enabled = Configs::getValueAsBool('timeline_photos_enabled');
		$timeline_photos_public = Configs::getValueAsBool('timeline_photos_public');
		$this->is_photo_timeline_enabled = $timeline_photos_enabled && ($is_logged_in || $timeline_photos_public);

		$this->is_mod_renamer_enabled = $this->isRenamerEnabled();
		$this->is_mod_webshop_enabled = $this->isWebshopEnabled();
	}

	private function checkModFrameEnabled(): bool
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
