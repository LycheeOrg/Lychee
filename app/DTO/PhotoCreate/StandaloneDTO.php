<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO\PhotoCreate;

use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Image\StreamStats;
use App\Contracts\Models\AbstractAlbum;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Metadata\Extractor;
use App\Models\Photo;

class StandaloneDTO implements PhotoDTO
{
	public ImageHandlerInterface|null $sourceImage = null;
	public AbstractSizeVariantNamingStrategy $namingStrategy;
	public TemporaryLocalFile|null $tmpVideoFile = null;
	public FlysystemFile $targetFile;
	public StreamStats|null $streamStat;
	public FlysystemFile|null $backupFile = null;

	public function __construct(
		// The resulting photo
		public Photo $photo,
		// The original photo source file that is imported.
		public readonly NativeLocalFile $sourceFile,
		// Indicates whether the new photo shall be starred.
		public readonly bool $is_starred,
		// The extracted EXIF information (populated during init phase).
		public readonly Extractor $exifInfo,
		// The intended parent album
		public readonly ?AbstractAlbum $album,
		// Indicates the intended owner of the image.
		public readonly int $intendedOwnerId,
		public readonly bool $shallImportViaSymlink,
		public readonly bool $shallDeleteImported,
	) {
	}

	public static function ofInit(InitDTO $initDTO): StandaloneDTO
	{
		return new StandaloneDTO(
			photo: new Photo(),
			sourceFile: $initDTO->sourceFile,
			is_starred: $initDTO->is_starred,
			exifInfo: $initDTO->exifInfo,
			album: $initDTO->album,
			intendedOwnerId: $initDTO->intendedOwnerId,
			shallImportViaSymlink: $initDTO->importMode->shallImportViaSymlink,
			shallDeleteImported: $initDTO->importMode->shallDeleteImported,
		);
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}
}
