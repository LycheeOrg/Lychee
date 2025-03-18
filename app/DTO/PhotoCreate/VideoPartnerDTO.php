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
		public readonly BaseMediaFile $video_file,
		// The resulting photo
		public readonly Photo $photo,
		public readonly bool $shall_import_via_symlink,
		public readonly bool $shall_delete_imported,
	) {
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}

	public static function ofInit(InitDTO $init_d_t_o): VideoPartnerDTO
	{
		return new VideoPartnerDTO(
			videoFile: $init_d_t_o->sourceFile,
			photo: $init_d_t_o->livePartner,
			shallImportViaSymlink: $init_d_t_o->importMode->shallImportViaSymlink,
			shallDeleteImported: $init_d_t_o->importMode->shallDeleteImported,
		);
	}
}
