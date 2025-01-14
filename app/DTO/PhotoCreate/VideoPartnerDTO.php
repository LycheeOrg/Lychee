<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO\PhotoCreate;

use App\Contracts\Image\StreamStats;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Image\Files\BaseMediaFile;
use App\Models\Photo;

class VideoPartnerDTO implements PhotoDTO
{
	public StreamStats|null $streamStat;
	public string $videoPath;

	public function __construct(
		public readonly BaseMediaFile $videoFile,
		// The resulting photo
		public readonly Photo $photo,
		public readonly bool $shallImportViaSymlink,
		public readonly bool $shallDeleteImported,
	) {
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}

	public static function ofInit(InitDTO $initDTO): VideoPartnerDTO
	{
		return new VideoPartnerDTO(
			videoFile: $initDTO->sourceFile,
			photo: $initDTO->livePartner,
			shallImportViaSymlink: $initDTO->importMode->shallImportViaSymlink,
			shallDeleteImported: $initDTO->importMode->shallDeleteImported,
		);
	}
}
