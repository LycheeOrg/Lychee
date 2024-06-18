<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreate\PhotoPartnerPipe;
use App\DTO\PhotoCreate\PhotoPartnerDTO;

class SetOldChecksum implements PhotoPartnerPipe
{
	public function handle(PhotoPartnerDTO $state, \Closure $next): PhotoPartnerDTO
	{
		// As the video is uploaded already, we must copy over the checksum
		$state->photo->live_photo_checksum = $state->oldVideo->checksum;

		return $next($state);
	}
}
