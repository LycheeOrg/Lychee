<?php

namespace App\DTO\PhotoCreate;

use App\Contracts\Models\AbstractAlbum;
use App\Metadata\Extractor;
use App\Models\Photo;

/**
 * DTO used when dealing with duplicates.
 * We only keep the needed datas.
 */
class DuplicateDTO
{
	public readonly bool $shallResyncMetadata;
	public readonly bool $shallSkipDuplicates;

	public bool $hasBeenReSynced;

	// Indicates the intended owner of the image.
	public readonly int $intendedOwnerId;

	// Indicates whether the new photo shall be starred.
	public readonly bool $is_starred;

	// The extracted EXIF information (populated during init phase).
	public readonly Extractor $exifInfo;

	// The intended parent album
	public readonly ?AbstractAlbum $album;

	// During initial steps if liveParner is found, it will be placed here.
	public Photo $photo;

	public function __construct(InitDTO $initDTO)
	{
		$this->shallResyncMetadata = $initDTO->importMode->shallResyncMetadata;
		$this->shallSkipDuplicates = $initDTO->importMode->shallSkipDuplicates;

		$this->photo = $initDTO->duplicate;
		$this->exifInfo = $initDTO->exifInfo;
		$this->album = $initDTO->album;
		$this->is_starred = $initDTO->is_starred;
		$this->intendedOwnerId = $initDTO->intendedOwnerId;
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
