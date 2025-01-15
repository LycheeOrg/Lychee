<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO\PhotoCreate;

use App\Contracts\Image\StreamStats;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Models\Photo;

class PhotoPartnerDTO implements PhotoDTO
{
	public StreamStats|null $streamStat;
	public string $videoPath;

	public function __construct(
		public readonly Photo $photo,
		public readonly Photo $oldVideo,
	) {
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}
}
