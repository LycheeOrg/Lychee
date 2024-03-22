<?php

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class GetVideoPath implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$photoFile = $state->photo->size_variants->getOriginal()->getFile();
		$photoPath = $photoFile->getRelativePath();
		$photoExt = $photoFile->getOriginalExtension();
		$videoExt = $state->videoFile->getOriginalExtension();
		$state->videoPath = substr($photoPath, 0, -strlen($photoExt)) . $videoExt;

		return $next($state);
	}
}
