<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Convert;

use App\Exceptions\CannotConvertMediaFileException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;
use App\Repositories\ConfigManager;

/**
 * Unified RAW-to-JPEG converter.
 *
 * Handles all convertible RAW formats (camera RAW, PSD, HEIC/HEIF) via Imagick.
 * Unlike HeifToJpeg, this converter does NOT delete the source file — the
 * original is preserved as a RAW size variant.
 */
class RawToJpeg
{
	public function __construct(
		private ConfigManager $config_manager,
	) {
	}

	/**
	 * Converts a RAW/HEIC/PSD file to JPEG using Imagick.
	 *
	 * Returns a TemporaryJobFile pointing to the new JPEG.
	 * The original source file is NOT deleted.
	 *
	 * @param NativeLocalFile $source_file the source file to convert
	 *
	 * @return TemporaryJobFile the converted JPEG file
	 *
	 * @throws CannotConvertMediaFileException if Imagick is unavailable or conversion fails
	 */
	public function handle(NativeLocalFile $source_file): TemporaryJobFile
	{
		if ($this->config_manager->hasImagick() === false) {
			throw new CannotConvertMediaFileException('Imagick is not available.');
		}

		$path = $source_file->getRealPath();
		$pathinfo = pathinfo($path);
		$file_name = $pathinfo['filename'];
		$new_path = $pathinfo['dirname'] . '/' . $file_name . '.jpg';

		try {
			$imagick_converted = new \Imagick($path);

			if ($imagick_converted->getNumberImages() > 1) {
				$imagick_converted->setIteratorIndex(0);
			}

			$imagick_converted->setImageFormat('jpeg');
			$imagick_converted->setImageCompression(\Imagick::COMPRESSION_JPEG);
			$imagick_converted->setImageCompressionQuality(92);

			$imagick_converted->autoOrient();
			$imagick_converted->writeImage($new_path);
		} catch (\ImagickException $e) {
			throw new CannotConvertMediaFileException('Failed to convert RAW file to JPEG.', $e);
		}

		// Note: source file is NOT deleted — it is preserved for RAW size variant.
		return new TemporaryJobFile($new_path);
	}
}
