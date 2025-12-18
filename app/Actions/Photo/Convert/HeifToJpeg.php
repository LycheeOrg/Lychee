<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Convert;

use App\Contracts\Image\ConvertMediaFileInterface;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;
use Safe\Exceptions\FilesystemException;
use function Safe\unlink;

class HeifToJpeg implements ConvertMediaFileInterface
{
	/**
	 * @throws \Exception
	 */
	public function handle(NativeLocalFile $tmp_file): TemporaryJobFile
	{
		try {
			$base_name = $tmp_file->getBasename();
			$path = $tmp_file->getRealPath();
			$pathinfo = pathinfo($path);
			$new_path = $pathinfo['dirname'] . '/' . $base_name . '.jpg';

			// Convert to Jpeg
			$imagick_converted = $this->convertToJpeg($path);

			// Store converted image
			$this->storeNewImage($imagick_converted, $new_path);

			// Delete old file
			$this->deleteOldFile($path);

			return new TemporaryJobFile($new_path);
		} catch (\Exception $e) {
			throw new \Exception('Failed to convert HEIC/HEIF to JPEG. ' . $e->getMessage());
		}
	}

	/**
	 * @throws \Exception
	 */
	public function storeNewImage(\Imagick $image_instance, string $store_to_path): void
	{
		try {
			$image_instance->writeImage($store_to_path);
		} catch (\ImagickException $e) {
			throw new \Exception('Failed to store converted image');
		}
	}

	/**
	 * @throws \Exception
	 */
	public function deleteOldFile(string $path): void
	{
		try {
			unlink($path);
		} catch (FilesystemException $e) {
			throw new \Exception('Failed to delete old file');
		}
	}

	/**
	 * @throws \Exception
	 */
	private function convertToJpeg(string $path): \Imagick
	{
		try {
			$img = new \Imagick($path);

			if ($img->getNumberImages() > 1) {
				$img->setIteratorIndex(0);
			}

			$img->setImageFormat('jpeg');
			$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
			$img->setImageCompressionQuality(92);

			$img->autoOrient();

			return $img;
		} catch (\ImagickException $e) {
			throw new \Exception('Failed to convert HEIC/HEIF to JPEG. ' . $e->getMessage());
		}
	}
}