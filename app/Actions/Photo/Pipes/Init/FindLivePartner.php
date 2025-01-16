<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Image\Files\BaseMediaFile;
use App\Models\Photo;

/**
 * Try to link live photo components together.
 */
class FindLivePartner implements InitPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		try {
			// find a potential partner which has the same content id
			if ($state->exifInfo->livePhotoContentID !== null) {
				$state->livePartner = Photo::query()
					->where('live_photo_content_id', '=', $state->exifInfo->livePhotoContentID)
					->where('album_id', '=', $state->album?->id)
					->whereNull('live_photo_short_path')->first();
			}

			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			if (
				$state->livePartner !== null && !(
					BaseMediaFile::isSupportedImageMimeType($state->exifInfo->type) && $state->livePartner->isVideo() ||
					BaseMediaFile::isSupportedVideoMimeType($state->exifInfo->type) && $state->livePartner->isPhoto()
				)
			) {
				$state->livePartner = null;
			}

			return $next($state);
		} catch (IllegalOrderOfOperationException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
	}
}

