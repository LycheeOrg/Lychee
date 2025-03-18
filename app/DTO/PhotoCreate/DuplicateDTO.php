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
	public bool $hasBeenReSynced;

	public function __construct(
		public readonly bool $shall_resync_metadata,
		public readonly bool $shall_skip_duplicates,
		// Indicates the intended owner of the image.
		public readonly int $intended_owner_id,

		// Indicates whether the new photo shall be starred.
		public readonly bool $is_starred,

		// The extracted EXIF information (populated during init phase).
		public readonly Extractor $exif_info,

		// The intended parent album
		public readonly ?AbstractAlbum $album,

		// During initial steps if duplicate is found, it will be placed here.
		public Photo $photo,
	) {
	}

	public static function ofInit(InitDTO $init_d_t_o): DuplicateDTO
	{
		return new DuplicateDTO(
			shallResyncMetadata: $init_d_t_o->importMode->shallResyncMetadata,
			shallSkipDuplicates: $init_d_t_o->importMode->shallSkipDuplicates,
			intendedOwnerId: $init_d_t_o->intendedOwnerId,
			is_starred: $init_d_t_o->is_starred,
			exifInfo: $init_d_t_o->exifInfo,
			album: $init_d_t_o->album,
			photo: $init_d_t_o->duplicate,
		);
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}

	public function setHasBeenResync(bool $val): void
	{
		$this->hasBeenReSynced = $val;
	}

	public function replicatePhoto(): void
	{
		$dup = $this->photo;
		$this->photo = $dup->replicate();
	}
}
