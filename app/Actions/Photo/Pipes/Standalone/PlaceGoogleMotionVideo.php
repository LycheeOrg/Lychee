<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Image\Files\FlysystemFile;

class PlaceGoogleMotionVideo implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// If we have a temporary video file from a Google Motion Picture,
		// we must move the preliminary extracted video file next to the
		// final target file
		if ($state->tmpVideoFile !== null) {
			$video_target_path =
				pathinfo($state->targetFile->getRelativePath(), PATHINFO_DIRNAME) .
				'/' .
				pathinfo($state->targetFile->getRelativePath(), PATHINFO_FILENAME) .
				$state->tmpVideoFile->getExtension();
			$video_target_file = new FlysystemFile($state->targetFile->getDisk(), $video_target_path);
			$video_target_file->write($state->tmpVideoFile->read());
			$state->photo->live_photo_short_path = $video_target_file->getRelativePath();
			$state->tmpVideoFile->close();
			$state->tmpVideoFile->delete();
			$state->tmpVideoFile = null;
		}

		return $next($state);
	}
}
