<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Actions\Photo\Convert\RawToJpeg;
use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\CannotConvertMediaFileException;
use App\Exceptions\Handler;
use App\Services\Image\FileExtensionService;

/**
 * Detects RAW/HEIC/PSD uploads, preserves the original for a RAW size variant,
 * and converts to JPEG for the ORIGINAL.
 *
 * Replaces the former `ConvertUnsupportedMedia` + `HeifToJpeg` pipeline.
 *
 * Behavior:
 * - If the file is a convertible RAW format: convert to JPEG, stash the
 *   original in $state->raw_source_file so `CreateRawSizeVariant` can store it.
 * - If conversion fails (graceful fallback): keep the file as-is,
 *   set raw_source_file = null (the file will be stored as accepted raw ORIGINAL
 *   by the existing flow), and log a warning.
 * - If the file is NOT a RAW format: pass through unchanged.
 */
class DetectAndStoreRaw implements InitPipe
{
	/**
	 * @param InitDTO                           $state
	 * @param \Closure(InitDTO $state): InitDTO $next
	 *
	 * @return InitDTO
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$ext = strtolower($state->source_file->getOriginalExtension());
		if (!str_starts_with($ext, '.')) {
			$ext = '.' . $ext;
		}

		// Only act on convertible RAW formats
		if (!in_array($ext, FileExtensionService::CONVERTIBLE_RAW_EXTENSIONS, true)) {
			return $next($state);
		}

		// Try to convert the RAW file to JPEG
		try {
			$raw_to_jpeg = resolve(RawToJpeg::class);
			$jpeg_file = $raw_to_jpeg->handle($state->source_file);

			// Stash the original file for CreateRawSizeVariant to pick up later
			$state->raw_source_file = $state->source_file;

			// Replace source with the converted JPEG for downstream processing
			$state->source_file = $jpeg_file;
		} catch (CannotConvertMediaFileException $e) {
			// Graceful fallback: keep the file as-is (accepted raw behavior).
			// The file will be stored as an ORIGINAL by AssertSupportedMedia
			// (which allows accepted raw formats).
			// We do NOT set raw_source_file — no RAW variant will be created.
			Handler::reportSafely($e);
		}

		return $next($state);
	}
}
