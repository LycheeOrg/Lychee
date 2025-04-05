<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO\PhotoCreate;

use App\Contracts\Models\AbstractAlbum;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Metadata\Extractor;
use App\Models\Photo;

/**
 * DTO used when dealing with duplicates.
 * We only keep the needed datas.
 */
class DuplicateDTO implements PhotoDTO
{
	public bool $has_been_resynced;

	public function __construct(
		public readonly bool $shall_resync_metadata,
		public readonly bool $shall_skip_duplicates,
		// Indicates the intended owner of the image.
		public readonly int $intended_owner_id,

		// Indicates whether the new photo shall be starred.
		public readonly bool $is_starred,

		// The extracted EXIF information (populated during init phase).
		public readonly ?Extractor $exif_info,

		// The intended parent album
		public readonly ?AbstractAlbum $album,

		// During initial steps if duplicate is found, it will be placed here.
		public Photo $photo,
	) {
	}

	public static function ofInit(InitDTO $init_dto): DuplicateDTO
	{
		return new DuplicateDTO(
			shall_resync_metadata: $init_dto->import_mode->shall_resync_metadata,
			shall_skip_duplicates: $init_dto->import_mode->shall_skip_duplicates,
			intended_owner_id: $init_dto->intended_owner_id,
			is_starred: $init_dto->is_starred,
			exif_info: $init_dto->exif_info,
			album: $init_dto->album,
			photo: $init_dto->duplicate,
		);
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}

	public function setHasBeenResync(bool $val): void
	{
		$this->has_been_resynced = $val;
	}

	public function replicatePhoto(): void
	{
		$dup = $this->photo;
		$this->photo = $dup->replicate();
	}
}
