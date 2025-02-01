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
 * {@link AddStrategyParameters::$exifInfo} attribute of the associated
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
			$state->photo->title = $state->exifInfo->title;
		}
		if ($state->photo->description === null) {
			$state->photo->description = $state->exifInfo->description;
		}
		if (count($state->photo->tags) === 0) {
			$state->photo->tags = $state->exifInfo->tags;
		}
		if ($state->photo->type === null) {
			$state->photo->type = $state->exifInfo->type;
		}
		if ($state->photo->iso === null) {
			$state->photo->iso = $state->exifInfo->iso;
		}
		if ($state->photo->aperture === null) {
			$state->photo->aperture = $state->exifInfo->aperture;
		}
		if ($state->photo->make === null) {
			$state->photo->make = $state->exifInfo->make;
		}
		if ($state->photo->model === null) {
			$state->photo->model = $state->exifInfo->model;
		}
		if ($state->photo->lens === null) {
			$state->photo->lens = $state->exifInfo->lens;
		}
		if ($state->photo->shutter === null) {
			$state->photo->shutter = $state->exifInfo->shutter;
		}
		if ($state->photo->focal === null) {
			$state->photo->focal = $state->exifInfo->focal;
		}
		if ($state->photo->taken_at === null) {
			$state->photo->taken_at = $state->exifInfo->taken_at;
			$state->photo->initial_taken_at = $state->exifInfo->taken_at;
		}
		if ($state->photo->latitude === null) {
			$state->photo->latitude = $state->exifInfo->latitude;
		}
		if ($state->photo->longitude === null) {
			$state->photo->longitude = $state->exifInfo->longitude;
		}
		if ($state->photo->altitude === null) {
			$state->photo->altitude = $state->exifInfo->altitude;
		}
		if ($state->photo->img_direction === null) {
			$state->photo->img_direction = $state->exifInfo->imgDirection;
		}
		if ($state->photo->location === null) {
			$state->photo->location = $state->exifInfo->location;
		}
		if ($state->photo->live_photo_content_id === null) {
			$state->photo->live_photo_content_id = $state->exifInfo->livePhotoContentID;
		}

		return $next($state);
	}
}