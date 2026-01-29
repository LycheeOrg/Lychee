<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PreComputedPhotoData extends Data
{
	public bool $is_video;
	public bool $is_raw;
	public bool $is_livephoto;
	public bool $is_camera_date;
	public bool $has_exif = false;
	public bool $has_location = false;
	public bool $is_taken_at_modified;
	public ?float $latitude = null;
	public ?float $longitude = null;
	public ?float $altitude = null;

	public function __construct(Photo $photo, bool $include_exif_data)
	{
		$this->is_video = $photo->isVideo();
		$this->is_raw = $photo->isRaw();
		$this->is_livephoto = $photo->live_photo_url !== null;
		$this->is_camera_date = $photo->taken_at !== null;
		// if taken_at is null, it is for sure not modified.
		// if taken_at is not null, then it is modified if initial_taken_at is null or if taken_at is different from initial_taken_at.
		$this->is_taken_at_modified = $photo->taken_at !== null && ($photo->initial_taken_at === null || $photo->taken_at->notEqualTo($photo->initial_taken_at));
		if (!$include_exif_data) {
			return;
		}
		$this->has_exif = $this->genExifHash($photo) !== '';
		$this->has_location = $this->has_location($photo);
		$this->set_gps_coordinates($photo);
	}

	private function has_location(Photo $photo): bool
	{
		return $photo->longitude !== null &&
			$photo->latitude !== null &&
			$photo->altitude !== null;
	}

	private function genExifHash(Photo $photo): string
	{
		$exif_hash = $photo->make;
		$exif_hash .= $photo->model;
		$exif_hash .= $photo->shutter;
		if (!$photo->isVideo()) {
			$exif_hash .= $photo->aperture;
			$exif_hash .= $photo->focal;
		}
		$exif_hash .= $photo->iso;

		return $exif_hash;
	}

	private function set_gps_coordinates(Photo $photo): void
	{
		if (request()->configs()->getValueAsBool('gps_coordinate_display') === false) {
			return;
		}
		if (Auth::guest() && request()->configs()->getValueAsBool('gps_coordinate_display_public') === false) {
			return;
		}

		$this->latitude = $photo->latitude;
		$this->longitude = $photo->longitude;
		$this->altitude = $photo->altitude;
	}
}
