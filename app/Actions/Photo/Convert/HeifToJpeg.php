<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Convert;

use App\Contracts\PhotoCreate\PhotoConverter;
use App\Exceptions\CannotConvertMediaFileException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;
use App\Repositories\ConfigManager;

class HeifToJpeg implements PhotoConverter
{
	public function __construct(
		private ConfigManager $config_manager,
	) {
	}

	/**
	 * @throws \Exception
	 */
	public function handle(NativeLocalFile $tmp_file): TemporaryJobFile
	{
		if ($this->config_manager->hasImagick() === false) {
			throw new CannotConvertMediaFileException('Imagick is not available.');
		}

		$path = $tmp_file->getRealPath();
		$pathinfo = pathinfo($path);
		$file_name = $pathinfo['filename'];
		$new_path = $pathinfo['dirname'] . '/' . $file_name . '.jpg';

		// Convert to Jpeg
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
			throw new CannotConvertMediaFileException('Failed to convert HEIC/HEIF to JPEG.', $e);
		}

		// Delete old file
		$tmp_file->delete();

		return new TemporaryJobFile($new_path);
	}
}
