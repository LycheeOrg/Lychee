<?php

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
				$videoHandler = new VideoHandler();
				$videoHandler->load($state->sourceFile);
				$position = is_numeric($state->photo->aperture) ? floatval($state->photo->aperture) / 2 : 0.0;
				$state->sourceImage = $videoHandler->extractFrame($position);
			} else {
				// Load source image if it is a supported photo format
				$state->sourceImage = new ImageHandler();
				$state->sourceImage->load($state->sourceFile);
			}
		} catch (\Throwable $e) {
			// This may happen for videos if FFmpeg is not available to
			// extract a still frame, or for raw files (Imagick may be
			// able to convert them to jpeg, but there are no
			// guarantees, and Imagick may not be available).
			$state->sourceImage = null;

			// Log an error without failing.
			Handler::reportSafely($e);
		}

		return $next($state);
	}
}

