<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO\PhotoCreate;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\Image\Files\NativeLocalFile;
use App\Metadata\Extractor;
use App\Models\Photo;

class InitDTO
{
	// Import mode.
	public readonly ImportMode $importMode;

	// Indicates the intended owner of the image.
	public readonly int $intendedOwnerId;

	// Indicates whether the new photo shall be starred.
	public bool $is_starred = false;

	// The extracted EXIF information (populated during init phase).
	public ?Extractor $exifInfo;

	// The intended parent album
	public ?AbstractAlbum $album = null;

	// The original photo source file that is imported.
	public NativeLocalFile $sourceFile;

	// During initial steps if a duplicate is found, it will be placed here.
	public Photo|null $duplicate = null;

	// During initial steps if liveParner is found, it will be placed here.
	public Photo|null $livePartner = null;

	// Optional last modified data if known.
	public int|null $fileLastModifiedTime = null;

	public function __construct(
		ImportParam $parameters,
		NativeLocalFile $sourceFile,
		AbstractAlbum|null $album,
		int|null $fileLastModifiedTime = null,
	) {
		$this->sourceFile = $sourceFile;
		$this->importMode = $parameters->importMode;
		$this->intendedOwnerId = $parameters->intendedOwnerId;
		$this->is_starred = $parameters->is_starred;
		$this->exifInfo = $parameters->exifInfo;
		$this->album = $album;
		$this->fileLastModifiedTime = $fileLastModifiedTime;
	}
}
