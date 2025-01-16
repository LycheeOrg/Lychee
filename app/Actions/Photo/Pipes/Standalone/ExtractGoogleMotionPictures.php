<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\Handler;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\GoogleMotionPictureHandler;

class ExtractGoogleMotionPictures implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if ($state->exifInfo->microVideoOffset === 0) {
			return $next($state);
		}

		// Handle Google Motion Pictures
		// We must extract the video stream from the original (local)
		// file and stash it away, before the original file is moved into
		// its (potentially remote) final position
		try {
			$state->tmpVideoFile = new TemporaryLocalFile(GoogleMotionPictureHandler::FINAL_VIDEO_FILE_EXTENSION, $state->sourceFile->getBasename());
			$gmpHandler = new GoogleMotionPictureHandler();
			$gmpHandler->load($state->sourceFile, $state->exifInfo->microVideoOffset);
			$gmpHandler->saveVideoStream($state->tmpVideoFile);
		} catch (\Throwable $e) {
			Handler::reportSafely($e);
			$state->tmpVideoFile = null;
		}

		return $next($state);
	}
}
