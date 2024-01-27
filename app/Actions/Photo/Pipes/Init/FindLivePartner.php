<?php

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Image\Files\BaseMediaFile;
use App\Models\Photo;

/**
 * Assert wether we support said file.
 */
class FindLivePartner implements PhotoCreatePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		try {
			// find a potential partner which has the same content id
			if ($state->parameters->exifInfo->livePhotoContentID !== null) {
				$state->livePartner = Photo::query()
					->where('live_photo_content_id', '=', $state->parameters->exifInfo->livePhotoContentID)
					->where('album_id', '=', $state->parameters->album?->id)
					->whereNull('live_photo_short_path')->first();
			}

			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			if (
				$state->livePartner !== null && !(
					BaseMediaFile::isSupportedImageMimeType($state->parameters->exifInfo->type) && $state->livePartner->isVideo() ||
					BaseMediaFile::isSupportedVideoMimeType($state->parameters->exifInfo->type) && $state->livePartner->isPhoto()
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

