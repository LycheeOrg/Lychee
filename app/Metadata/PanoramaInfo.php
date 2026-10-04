<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata;

/**
 * Result of {@see PanoramaDetector::detect()}.
 *
 * The crop fields are in pixels of the file the photo was read from; all
 * four are null for a full sphere and for a flat photo.
 */
final readonly class PanoramaInfo
{
	public function __construct(
		public bool $is_360,
		public ?int $full_width = null,
		public ?int $full_height = null,
		public ?int $crop_left = null,
		public ?int $crop_top = null,
	) {
	}

	public static function flat(): self
	{
		return new self(false);
	}

	public static function fullSphere(): self
	{
		return new self(true);
	}
}
