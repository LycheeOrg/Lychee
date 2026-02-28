<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
use Illuminate\Support\Collection;

class StandaloneDTO implements PhotoDTO
{
	public ImageHandlerInterface|null $source_image = null;
	public AbstractSizeVariantNamingStrategy $naming_strategy;
	public TemporaryLocalFile|null $tmp_video_file = null;
	public FlysystemFile $target_file;
	public StreamStats|null $stream_stat;
	public FlysystemFile|null $backup_file = null;
	public Collection $tags;

	// If the uploaded file is a RAW format, this holds the original (untouched) source file
	// that should be preserved as a RAW size variant after conversion to JPEG.
	public NativeLocalFile|null $raw_source_file = null;

	public function __construct(
		// The resulting photo
		public Photo $photo,
		// The original photo source file that is imported.
		public readonly NativeLocalFile $source_file,
		// Indicates whether the new photo shall be highlighted.
		public readonly bool $is_highlighted,
		// The extracted EXIF information (populated during init phase).
		public readonly Extractor $exif_info,
		// The intended parent album
		public readonly ?AbstractAlbum $album,
		// Indicates the intended owner of the image.
		public readonly int $intended_owner_id,
		public readonly bool $shall_import_via_symlink,
		public readonly bool $shall_delete_imported,
		public readonly bool $shall_rename_photo_title,
		// Whether to apply watermark (null = use global setting, true = force apply, false = skip).
		public readonly ?bool $apply_watermark,
	) {
		$this->tags = new Collection();
	}

	public static function ofInit(InitDTO $init_dto): StandaloneDTO
	{
		$dto = new StandaloneDTO(
			photo: new Photo(),
			source_file: $init_dto->source_file,
			is_highlighted: $init_dto->is_highlighted,
			exif_info: $init_dto->exif_info,
			album: $init_dto->album,
			intended_owner_id: $init_dto->intended_owner_id,
			shall_import_via_symlink: $init_dto->import_mode->shall_import_via_symlink,
			shall_delete_imported: $init_dto->import_mode->shall_delete_imported,
			shall_rename_photo_title: $init_dto->import_mode->shall_rename_photo_title,
			apply_watermark: $init_dto->apply_watermark,
		);
		$dto->raw_source_file = $init_dto->raw_source_file;

		return $dto;
	}

	public function getPhoto(): Photo
	{
		return $this->photo;
	}

	public function getTags(): Collection
	{
		return $this->tags;
	}
}
