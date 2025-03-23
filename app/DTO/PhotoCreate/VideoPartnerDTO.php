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
	public StreamStats|null $stream_stat;
	public string $video_path;

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

	public static function ofInit(InitDTO $init_dto): VideoPartnerDTO
	{
		return new VideoPartnerDTO(
			video_file: $init_dto->source_file,
			photo: $init_dto->live_partner,
			shall_import_via_symlink: $init_dto->import_mode->shall_import_via_symlink,
			shall_delete_imported: $init_dto->import_mode->shall_delete_imported,
		);
	}
}
