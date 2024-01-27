<?php

namespace App\Actions\Photo\Pipes;

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
			if ($state->strategyParameters->exifInfo->livePhotoContentID !== null) {
				/** @var Photo|null $livePartner */
				$livePartner = Photo::query()
					->where('live_photo_content_id', '=', $state->strategyParameters->exifInfo->livePhotoContentID)
					->where('album_id', '=', $state->strategyParameters->album?->id)
					->whereNull('live_photo_short_path')->first();
			}
			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			if (
				$livePartner !== null && !(
					BaseMediaFile::isSupportedImageMimeType($state->strategyParameters->exifInfo->type) && $livePartner->isVideo() ||
					BaseMediaFile::isSupportedVideoMimeType($state->strategyParameters->exifInfo->type) && $livePartner->isPhoto()
				)
			) {
				$livePartner = null;
			}

			$state->livePartner = $livePartner;

			return $next($state);
		} catch (IllegalOrderOfOperationException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
	}
}

