<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\Services\Image\FileExtensionService;

/**
 * One row of the Insights projection (Feature 085, NFR-085-06), with the
 * driver-specific values (booleans as 0/1 or true/false, numbers as strings)
 * normalised.
 */
final readonly class PhotoRow
{
	public function __construct(
		public string $id,
		public ?string $taken_at,
		public ?string $taken_at_orig_tz,
		public bool $is_image,
		public bool $is_video,
		public ?string $make,
		public ?string $model,
		public ?string $lens,
		public ?string $iso,
		public ?string $aperture,
		public ?string $shutter,
		public ?string $focal,
		public ?string $duration,
		public int $filesize,
		public int $width,
		public int $height,
		public int $face_count,
		public bool $is_located,
		public bool $is_highlighted,
		public bool $in_album,
	) {
	}

	public static function fromDatabase(object $row): self
	{
		return new self(
			id: strval($row->id),
			taken_at: $row->taken_at,
			taken_at_orig_tz: $row->taken_at_orig_tz,
			is_image: in_array($row->type, FileExtensionService::SUPPORTED_IMAGE_MIME_TYPES, true),
			is_video: in_array($row->type, FileExtensionService::SUPPORTED_VIDEO_MIME_TYPES, true),
			make: $row->make,
			model: $row->model,
			lens: $row->lens,
			iso: $row->iso,
			aperture: $row->aperture,
			shutter: $row->shutter,
			focal: $row->focal,
			duration: $row->duration,
			filesize: intval($row->filesize ?? 0),
			width: intval($row->width ?? 0),
			height: intval($row->height ?? 0),
			face_count: intval($row->face_count),
			is_located: $row->latitude !== null && $row->longitude !== null,
			is_highlighted: boolval($row->is_highlighted),
			in_album: boolval($row->in_album),
		);
	}
}
