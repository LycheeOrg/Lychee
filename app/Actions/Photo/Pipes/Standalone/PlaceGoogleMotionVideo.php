<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\DTO\PhotoCreate\StandaloneDTO;
use App\Image\Files\FlysystemFile;
use App\Image\Handlers\GoogleMotionPictureHandler;

class PlaceGoogleMotionVideo extends AbstractStandalonePipe
{
	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// If we have a temporary video file from a Google Motion Picture,
		// we must move the preliminary extracted video file next to the
		// final target file
		if ($state->tmp_video_file !== null) {
			$video_target_path = GoogleMotionPictureHandler::videoPathNextTo($state->target_file->getRelativePath());
			$video_target_file = new FlysystemFile($state->target_file->getDisk(), $video_target_path);
			$video_target_file->write($state->tmp_video_file->read());
			$state->photo->live_photo_short_path = $video_target_file->getRelativePath();
			$state->tmp_video_file->close();
			$state->tmp_video_file->delete();
			$state->tmp_video_file = null;
		}

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.place_google_motion_video';
	}
}
