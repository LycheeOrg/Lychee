<?php

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class SetOldChecksum implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// If the video is uploaded already, we must copy over the checksum
		$state->photo->live_photo_checksum = $state->oldVideo->checksum;

		return $next($state);
	}
}
