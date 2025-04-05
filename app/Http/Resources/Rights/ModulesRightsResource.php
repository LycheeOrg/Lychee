<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ModulesRightsResource extends Data
{
	public bool $is_map_enabled = false;
	public bool $is_mod_frame_enabled = false;
	public bool $is_photo_timeline_enabled = false;

	public function __construct()
	{
		$is_logged_in = Auth::check();
		$count_locations = Photo::whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
		$map_display = Configs::getValueAsBool('map_display');
		$public_display = $is_logged_in || Configs::getValueAsBool('map_display_public');

		$this->is_map_enabled = $count_locations && $map_display && $public_display;
		$this->is_mod_frame_enabled = $this->checkModFrameEnabled();

		$timeline_photos_enabled = Configs::getValueAsBool('timeline_photos_enabled');
		$timeline_photos_public = Configs::getValueAsBool('timeline_photos_public');
		$this->is_photo_timeline_enabled = $timeline_photos_enabled && ($is_logged_in || $timeline_photos_public);
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
}
