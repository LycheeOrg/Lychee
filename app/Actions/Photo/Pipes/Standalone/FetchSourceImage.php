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
use App\Image\Handlers\ImageHandler;
use App\Image\Handlers\VideoHandler;

class FetchSourceImage implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		try {
			if ($state->photo->isVideo()) {
				$video_handler = new VideoHandler();
				$video_handler->load($state->source_file);
				$position = is_numeric($state->photo->aperture) ? floatval($state->photo->aperture) / 2 : 0.0;
				$state->source_image = $video_handler->extractFrame($position);
			} else {
				// Load source image if it is a supported photo format
				$state->source_image = new ImageHandler();
				$state->source_image->load($state->source_file);
			}
		} catch (\Throwable $t) {
			// This may happen for videos if FFmpeg is not available to
			// extract a still frame, or for raw files (Imagick may be
			// able to convert them to jpeg, but there are no
			// guarantees, and Imagick may not be available).
			$state->source_image = null;

			// Log an error without failing.
			Handler::reportSafely($t);
		}

		return $next($state);
	}
}

