<?php

/**
 * SPDX-License-Identifier: MIT
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
	public readonly ImportMode $import_mode;

	// Indicates the intended owner of the image.
	public readonly int $intended_owner_id;

	// Indicates whether the new photo shall be starred.
	public bool $is_starred = false;

	// The extracted EXIF information (populated during init phase).
	public ?Extractor $exif_info;

	// The intended parent album
	public ?AbstractAlbum $album = null;

	// The original photo source file that is imported.
	public NativeLocalFile $source_file;

	// During initial steps if a duplicate is found, it will be placed here.
	public Photo|null $duplicate = null;

	// During initial steps if liveParner is found, it will be placed here.
	public Photo|null $live_partner = null;

	// Optional last modified data if known.
	public int|null $file_last_modified_time = null;

	public function __construct(
		ImportParam $parameters,
		NativeLocalFile $source_file,
		AbstractAlbum|null $album,
		int|null $file_last_modified_time = null,
	) {
		$this->source_file = $source_file;
		$this->import_mode = $parameters->import_mode;
		$this->intended_owner_id = $parameters->intended_owner_id;
		$this->is_starred = $parameters->is_starred;
		$this->exif_info = $parameters->exif_info;
		$this->album = $album;
		$this->file_last_modified_time = $file_last_modified_time;
	}
}
