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
		$imageDim = $state->sourceImage?->isLoaded() ?
			$state->sourceImage->getDimensions() :
			new ImageDimension($state->exifInfo->width, $state->exifInfo->height);

		$state->photo->size_variants->create(
			SizeVariantType::ORIGINAL,
			$state->targetFile->getRelativePath(),
			$imageDim,
			$state->streamStat->bytes
		);

		return $next($state);
	}
}
