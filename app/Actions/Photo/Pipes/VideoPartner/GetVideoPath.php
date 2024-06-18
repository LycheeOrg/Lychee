<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreate\VideoPartnerPipe;
use App\DTO\PhotoCreate\VideoPartnerDTO;

class GetVideoPath implements VideoPartnerPipe
{
	public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO
	{
		$photoFile = $state->photo->size_variants->getOriginal()->getFile();
		$photoPath = $photoFile->getRelativePath();
		$photoExt = $photoFile->getOriginalExtension();
		$videoExt = $state->videoFile->getOriginalExtension();
		$state->videoPath = substr($photoPath, 0, -strlen($photoExt)) . $videoExt;

		return $next($state);
	}
}
