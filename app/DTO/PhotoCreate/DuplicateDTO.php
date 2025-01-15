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
		public readonly bool $shallResyncMetadata,
		public readonly bool $shallSkipDuplicates,
		// Indicates the intended owner of the image.
		public readonly int $intendedOwnerId,

		// Indicates whether the new photo shall be starred.
		public readonly bool $is_starred,

		// The extracted EXIF information (populated during init phase).
		public readonly Extractor $exifInfo,

		// The intended parent album
		public readonly ?AbstractAlbum $album,

		// During initial steps if duplicate is found, it will be placed here.
		public Photo $photo,
	) {
	}

	public static function ofInit(InitDTO $initDTO): DuplicateDTO
	{
		return new DuplicateDTO(
			shallResyncMetadata: $initDTO->importMode->shallResyncMetadata,
			shallSkipDuplicates: $initDTO->importMode->shallSkipDuplicates,
			intendedOwnerId: $initDTO->intendedOwnerId,
			is_starred: $initDTO->is_starred,
			exifInfo: $initDTO->exifInfo,
			album: $initDTO->album,
			photo: $initDTO->duplicate,
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
