<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreate\VideoPartnerPipe;
use App\DTO\PhotoCreate\VideoPartnerDTO;

class GetVideoPath implements VideoPartnerPipe
{
	public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO
	{
		$photo_file = $state->photo->size_variants->getOriginal()->getFile();
		$photo_path = $photo_file->getRelativePath();
		$photo_ext = $photo_file->getOriginalExtension();
		$video_ext = $state->video_file->getOriginalExtension();
		$state->video_path = substr($photo_path, 0, -strlen($photo_ext)) . $video_ext;

		return $next($state);
	}
}
