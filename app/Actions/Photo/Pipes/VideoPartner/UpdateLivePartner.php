<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreate\VideoPartnerPipe;
use App\DTO\PhotoCreate\VideoPartnerDTO;
use App\Image\StreamStat;

class UpdateLivePartner implements VideoPartnerPipe
{
	public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO
	{
		/** @var StreamStat|null $stat */
		$stat = $state->stream_stat;

		$state->photo->live_photo_short_path = $state->video_path;
		$state->photo->live_photo_checksum = $stat?->checksum;

		return $next($state);
	}
}