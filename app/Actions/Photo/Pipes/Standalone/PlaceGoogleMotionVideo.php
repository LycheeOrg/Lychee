<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Image\Files\FlysystemFile;

class PlaceGoogleMotionVideo implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// If we have a temporary video file from a Google Motion Picture,
		// we must move the preliminary extracted video file next to the
		// final target file
		if ($state->tmpVideoFile !== null) {
			$videoTargetPath =
				pathinfo($state->targetFile->getRelativePath(), PATHINFO_DIRNAME) .
				'/' .
				pathinfo($state->targetFile->getRelativePath(), PATHINFO_FILENAME) .
				$state->tmpVideoFile->getExtension();
			$videoTargetFile = new FlysystemFile($state->targetFile->getDisk(), $videoTargetPath);
			$videoTargetFile->write($state->tmpVideoFile->read());
			$state->photo->live_photo_short_path = $videoTargetFile->getRelativePath();
			$state->tmpVideoFile->close();
			$state->tmpVideoFile->delete();
			$state->tmpVideoFile = null;
		}

		return $next($state);
	}
}
