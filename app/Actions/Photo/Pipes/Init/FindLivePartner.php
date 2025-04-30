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
			if ($state->exif_info->live_photo_content_id !== null) {
				$state->live_partner = Photo::query()
					->where('live_photo_content_id', '=', $state->exif_info->live_photo_content_id)
					->where('album_id', '=', $state->album?->get_id())
					->whereNull('live_photo_short_path')->first();
			}

			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			if (
				$state->live_partner !== null && !(
					BaseMediaFile::isSupportedImageMimeType($state->exif_info->type) && $state->live_partner->isVideo() ||
					BaseMediaFile::isSupportedVideoMimeType($state->exif_info->type) && $state->live_partner->isPhoto()
				)
			) {
				$state->live_partner = null;
			}

			return $next($state);
			// @codeCoverageIgnoreStart
		} catch (IllegalOrderOfOperationException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}

