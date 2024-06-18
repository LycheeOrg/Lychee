<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreate\VideoPartnerPipe;
use App\DTO\PhotoCreate\VideoPartnerDTO;

class UpdateLivePartner implements VideoPartnerPipe
{
	public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO
	{
		$state->photo->live_photo_short_path = $state->videoPath;
		$state->photo->live_photo_checksum = $state->streamStat?->checksum;

		return $next($state);
	}
}
