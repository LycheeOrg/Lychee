<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Hydrates meta-info of the media file from the
 * {@link AddStrategyParameters::$exif_info} attribute of the associated
 * {@link AddStrategyParameters} object into the associated {@link Photo}
 * object.
 *
 * Meta information is conditionally copied if and only if the target
 * attribute of the {@link Photo} object is null or empty and the
 * meta-info is not.
 * This way this method is usable by {@link AddStandaloneStrategy} and
 * {@link AddDuplicateStrategy}.
 * For a freshly created {@link Photo} object (with empty attributes)
 * all available meta-data is hydrated, but for an already existing
 * {@link Photo} object existing attributes are not overwritten.
 */
class HydrateMetadata implements SharedPipe
{
	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		if ($state->photo->title === null) {
			$state->photo->title = $state->exif_info->title;
		}
		if ($state->photo->description === null) {
			$state->photo->description = $state->exif_info->description;
		}
		if (count($state->photo->tags) === 0) {
			$state->photo->tags = $state->exif_info->tags;
		}
		if ($state->photo->type === null) {
			$state->photo->type = $state->exif_info->type;
		}
		if ($state->photo->iso === null) {
			$state->photo->iso = $state->exif_info->iso;
		}
		if ($state->photo->aperture === null) {
			$state->photo->aperture = $state->exif_info->aperture;
		}
		if ($state->photo->make === null) {
			$state->photo->make = $state->exif_info->make;
		}
		if ($state->photo->model === null) {
			$state->photo->model = $state->exif_info->model;
		}
		if ($state->photo->lens === null) {
			$state->photo->lens = $state->exif_info->lens;
		}
		if ($state->photo->shutter === null) {
			$state->photo->shutter = $state->exif_info->shutter;
		}
		if ($state->photo->focal === null) {
			$state->photo->focal = $state->exif_info->focal;
		}
		if ($state->photo->taken_at === null) {
			$state->photo->taken_at = $state->exif_info->taken_at;
			$state->photo->initial_taken_at = $state->exif_info->taken_at;
		}
		if ($state->photo->latitude === null) {
			$state->photo->latitude = $state->exif_info->latitude;
		}
		if ($state->photo->longitude === null) {
			$state->photo->longitude = $state->exif_info->longitude;
		}
		if ($state->photo->altitude === null) {
			$state->photo->altitude = $state->exif_info->altitude;
		}
		if ($state->photo->img_direction === null) {
			$state->photo->img_direction = $state->exif_info->img_direction;
		}
		if ($state->photo->location === null) {
			$state->photo->location = $state->exif_info->location;
		}
		if ($state->photo->live_photo_content_id === null) {
			$state->photo->live_photo_content_id = $state->exif_info->live_photo_content_id;
		}

		return $next($state);
	}
}