<?php

/**
 * SPDX-License-Identifier: MIT
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
		if ($state->exif_info->micro_video_offset === 0) {
			return $next($state);
		}

		// Handle Google Motion Pictures
		// We must extract the video stream from the original (local)
		// file and stash it away, before the original file is moved into
		// its (potentially remote) final position
		try {
			$state->tmp_video_file = new TemporaryLocalFile(GoogleMotionPictureHandler::FINAL_VIDEO_FILE_EXTENSION, $state->source_file->getBasename());
			$gmp_handler = new GoogleMotionPictureHandler();
			$gmp_handler->load($state->source_file, $state->exif_info->micro_video_offset);
			$gmp_handler->saveVideoStream($state->tmp_video_file);
		} catch (\Throwable $e) {
			Handler::reportSafely($e);
			$state->tmp_video_file = null;
		}

		return $next($state);
	}
}
