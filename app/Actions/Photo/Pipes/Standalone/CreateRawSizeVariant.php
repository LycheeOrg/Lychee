<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\CreateSizeVariantFlags;
use App\DTO\ImageDimension;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\SizeVariantType;

/**
 * Creates the RAW size variant if the upload included a convertible RAW file.
 *
 * This pipe runs after CreateOriginalSizeVariant. It checks whether the
 * Init pipe DetectAndStoreRaw stashed a raw source file in the DTO.
 * If so, it copies the original (untouched) file to storage and creates
 * the RAW row in `size_variants`.
 */
class CreateRawSizeVariant implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if ($state->raw_source_file === null) {
			return $next($state);
		}

		// Temporarily switch the naming strategy extension to the RAW file extension
		$raw_extension = $state->raw_source_file->getOriginalExtension();
		$original_extension = $state->naming_strategy->getExtension();
		$state->naming_strategy->setExtension($raw_extension);

		// Create target file for the RAW variant
		$raw_target = $state->naming_strategy->createFile(SizeVariantType::RAW, new CreateSizeVariantFlags());

		// Write the raw source content into the target
		$stream_stat = $raw_target->write($state->raw_source_file->read(), true);
		$state->raw_source_file->close();
		$raw_target->close();

		// Restore the original extension for subsequent pipes
		$state->naming_strategy->setExtension($original_extension);

		$dimensions = new ImageDimension(0, 0);

		// Load the dimension from the previous Original which was created.
		$original_jpeg = $state->photo->size_variants->getOriginal();
		if ($original_jpeg !== null) {
			$dimensions = new ImageDimension($original_jpeg->width, $original_jpeg->height);
		}

		// Create the RAW size variant DB record
		// RAW files have no displayable dimensions (use 0x0)
		$state->photo->size_variants->create(
			SizeVariantType::RAW,
			$raw_target->getRelativePath(),
			$dimensions,
			$stream_stat->bytes
		);

		// Clean up the raw source file if the import mode requests deletion
		if ($state->shall_delete_imported) {
			try {
				$state->raw_source_file->delete();
			} catch (\Throwable $e) {
				// Non-fatal: fall back to copy semantics
				\App\Exceptions\Handler::reportSafely($e);
			}
		}

		return $next($state);
	}
}
