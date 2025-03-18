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
		public readonly NativeLocalFile $source_file,
		// Indicates whether the new photo shall be starred.
		public readonly bool $is_starred,
		// The extracted EXIF information (populated during init phase).
		public readonly Extractor $exif_info,
		// The intended parent album
		public readonly ?AbstractAlbum $album,
		// Indicates the intended owner of the image.
		public readonly int $intended_owner_id,
		public readonly bool $shall_import_via_symlink,
		public readonly bool $shall_delete_imported,
	) {
	}

	public static function ofInit(InitDTO $init_d_t_o): StandaloneDTO
	{
		return new StandaloneDTO(
			photo: new Photo(),
			sourceFile: $init_d_t_o->sourceFile,
			is_starred: $init_d_t_o->is_starred,
			exifInfo: $init_d_t_o->exifInfo,
			album: $init_d_t_o->album,
			intendedOwnerId: $init_d_t_o->intendedOwnerId,
			shallImportViaSymlink: $init_d_t_o->importMode->shallImportViaSymlink,
			shallDeleteImported: $init_d_t_o->importMode->shallDeleteImported,
		);
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}
}
