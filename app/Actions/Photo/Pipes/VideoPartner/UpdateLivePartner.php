<?php

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class UpdateLivePartner implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->photo->live_photo_short_path = $state->videoPath;
		$state->photo->live_photo_checksum = $state->streamStat?->checksum;

		return $next($state);
	}
}
