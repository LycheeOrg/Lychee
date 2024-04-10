<?php

namespace App\DTO\PhotoCreate;

use App\Contracts\Image\StreamStats;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Image\Files\BaseMediaFile;
use App\Models\Photo;

class VideoPartnerDTO implements PhotoDTO
{
	public readonly bool $shallImportViaSymlink;
	public readonly bool $shallDeleteImported;

	// The resulting photo
	public readonly Photo $photo;

	public StreamStats|null $streamStat;

	public string $videoPath;
	public readonly BaseMediaFile $videoFile;

	public function __construct(InitDTO $initDTO)
	{
		$this->videoFile = $initDTO->sourceFile;
		$this->photo = $initDTO->livePartner;
		$this->shallImportViaSymlink = $initDTO->importMode->shallImportViaSymlink;
		$this->shallDeleteImported = $initDTO->importMode->shallDeleteImported;
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}
}
