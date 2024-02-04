<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class SetChecksum implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// The original and final checksum may differ, if the photo has
		// been rotated by `putSourceIntoFinalDestination` while being
		// moved into final position.
		$state->photo->checksum = $state->streamStat->checksum;

		return $next($state);
	}
}
