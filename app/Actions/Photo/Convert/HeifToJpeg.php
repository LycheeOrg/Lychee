<?php

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
	public function handle(NativeLocalFile $tmpFile): TemporaryJobFile
	{
		try {
			$baseName = $tmpFile->getBasename();
			$path = $tmpFile->getRealPath();
			$pathinfo = pathinfo($path);
			$newPath = $pathinfo['dirname'] . '/' . $baseName . '.jpg';

			// Convert to Jpeg
			$imagickConverted = $this->convertToJpeg($path);

			// Store converted image
			$this->storeNewImage($imagickConverted, $newPath);

			// Delete old file
			$this->deleteOldFile($path);

			return new TemporaryJobFile($newPath);
		} catch (\Exception $e) {
			throw new \Exception('Failed to convert HEIC/HEIF to JPEG. ' . $e->getMessage());
		}
	}

	/**
	 * @throws \Exception
	 */
	public function storeNewImage(\Imagick $imageInstance, string $storeToPath): void
	{
		try {
			$imageInstance->writeImage($storeToPath);
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