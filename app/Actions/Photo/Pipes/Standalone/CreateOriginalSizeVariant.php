<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreatePipe;
use App\DTO\ImageDimension;
use App\DTO\PhotoCreateDTO;
use App\Enum\SizeVariantType;

class CreateOriginalSizeVariant implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// Create original size variant of photo
		// If the image has been loaded (and potentially auto-rotated)
		// take the dimension from the image.
		// As a fallback for media files from which no image could be extracted (e.g. unsupported file formats) we use the EXIF data.
		$imageDim = $state->sourceImage?->isLoaded() ?
			$state->sourceImage->getDimensions() :
			new ImageDimension($state->parameters->exifInfo->width, $state->parameters->exifInfo->height);
		$state->photo->size_variants->create(
			SizeVariantType::ORIGINAL,
			$state->targetFile->getRelativePath(),
			$imageDim,
			$state->streamStat->bytes
		);

		return $next($state);
	}
}
