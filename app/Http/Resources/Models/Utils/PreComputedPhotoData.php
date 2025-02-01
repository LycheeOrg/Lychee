<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PreComputedPhotoData extends Data
{
	public bool $is_video;
	public bool $is_raw;
	public bool $is_livephoto;
	public bool $is_camera_date;
	public bool $has_exif;
	public bool $has_location;
	public bool $is_taken_at_modified;

	public function __construct(Photo $photo)
	{
		$this->is_video = $photo->isVideo();
		$this->is_raw = $photo->isRaw();
		$this->is_livephoto = $photo->live_photo_url !== null;
		$this->is_camera_date = $photo->taken_at !== null;
		$this->has_exif = $this->genExifHash($photo) !== '';
		$this->has_location = $this->has_location($photo);
		// if taken_at is null, it is for sure not modified.
		// if taken_at is not null, then it is modified if initial_taken_at is null or if taken_at is different from initial_taken_at.
		// dd($photo->taken_at, $photo->initial_taken_at, $photo->taken_at->notEqualTo($photo->initial_taken_at));
		$this->is_taken_at_modified = $photo->taken_at !== null && ($photo->initial_taken_at === null || $photo->taken_at->notEqualTo($photo->initial_taken_at));
	}

	private function has_location(Photo $photo): bool
	{
		return $photo->longitude !== null &&
			$photo->latitude !== null &&
			$photo->altitude !== null;
	}

	private function genExifHash(Photo $photo): string
	{
		$exifHash = $photo->make;
		$exifHash .= $photo->model;
		$exifHash .= $photo->shutter;
		if (!$photo->isVideo()) {
			$exifHash .= $photo->aperture;
			$exifHash .= $photo->focal;
		}
		$exifHash .= $photo->iso;

		return $exifHash;
	}
}
