<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\ImageDimension;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\SizeVariantType;

class CreateOriginalSizeVariant implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Create original size variant of photo
		// If the image has been loaded (and potentially auto-rotated)
		// take the dimension from the image.
		// As a fallback for media files from which no image could be extracted (e.g. unsupported file formats) we use the EXIF data.
		$image_dim = $state->source_image?->isLoaded() ?
			$state->source_image->getDimensions() :
			new ImageDimension($state->exif_info->width, $state->exif_info->height);

		$state->photo->size_variants->create(
			SizeVariantType::ORIGINAL,
			$state->target_file->getRelativePath(),
			$image_dim,
			$state->stream_stat->bytes
		);

		return $next($state);
	}
}
