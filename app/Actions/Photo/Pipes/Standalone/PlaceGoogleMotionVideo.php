<?php

/**
 * SPDX-License-Identifier: MIT
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
		if ($state->tmp_video_file !== null) {
			$video_target_path =
				pathinfo($state->target_file->getRelativePath(), PATHINFO_DIRNAME) .
				'/' .
				pathinfo($state->target_file->getRelativePath(), PATHINFO_FILENAME) .
				$state->tmp_video_file->getExtension();
			$video_target_file = new FlysystemFile($state->target_file->getDisk(), $video_target_path);
			$video_target_file->write($state->tmp_video_file->read());
			$state->photo->live_photo_short_path = $video_target_file->getRelativePath();
			$state->tmp_video_file->close();
			$state->tmp_video_file->delete();
			$state->tmp_video_file = null;
		}

		return $next($state);
	}
}
