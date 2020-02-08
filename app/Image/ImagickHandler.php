<?php

namespace App\Image;

use App\Logs;
use ImagickException;

class ImagickHandler implements ImageHandlerInterface
{
	/**
	 * @var int
	 */
	private $compressionQuality;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(int $compressionQuality)
	{
		$this->compressionQuality = $compressionQuality;
	}

	/**
	 * {@inheritdoc}
	 */
	public function scale(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight,
		int &$resWidth,
		int &$resHeight
	): bool {
		try {
			// Read image
			$image = new \Imagick();
			$image->readImage($source);
			$image->setImageCompressionQuality($this->compressionQuality);

			$profiles = $image->getImageProfiles('icc', true);

			// Remove metadata to save some bytes
			$image->stripImage();

			if (!empty($profiles)) {
				$image->profileImage('icc', $profiles['icc']);
			}

			$image->scaleImage($newWidth, $newHeight, ($newWidth != 0 && $newHeight != 0));
			$image->writeImage($destination);
			Logs::notice(__METHOD__, __LINE__, 'Saving thumb to ' . $destination);
			$resWidth = $image->getImageWidth();
			$resHeight = $image->getImageHeight();
			$image->clear();
			$image->destroy();
		} catch (ImagickException $exception) {
			Logs::error(__METHOD__, __LINE__, $exception->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight
	): bool {
		try {
			$image = new \Imagick();
			$image->readImage($source);
			$image->setImageCompressionQuality($this->compressionQuality);

			$profiles = $image->getImageProfiles('icc', true);

			// Remove metadata to save some bytes
			$image->stripImage();

			if (!empty($profiles)) {
				$image->profileImage('icc', $profiles['icc']);
			}

			$image->cropThumbnailImage($newWidth, $newHeight);
			$image->writeImage($destination);
			Logs::notice(__METHOD__, __LINE__, 'Saving thumb to ' . $destination);
			$image->clear();
			$image->destroy();
		} catch (ImagickException $exception) {
			Logs::error(__METHOD__, __LINE__, $exception->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function autoRotate(string $path, array $info): array
	{
		try {
			$image = new \Imagick();
			$image->readImage($path);

			$orientation = $image->getImageOrientation();

			$rotate = true;
			switch ($orientation) {
					case \Imagick::ORIENTATION_TOPLEFT:
						$rotate = false;
						break;
					case \Imagick::ORIENTATION_TOPRIGHT:
						$image->flopImage();
						break;
					case \Imagick::ORIENTATION_BOTTOMRIGHT:
						$image->rotateImage(new \ImagickPixel(), 180);
						break;
					case \Imagick::ORIENTATION_BOTTOMLEFT:
						$image->flopImage();
						$image->rotateImage(new \ImagickPixel(), 180);
						break;
					case \Imagick::ORIENTATION_LEFTTOP:
						$image->flopImage();
						$image->rotateImage(new \ImagickPixel(), -90);
						break;
					case \Imagick::ORIENTATION_RIGHTTOP:
						$image->rotateImage(new \ImagickPixel(), 90);
						break;
					case \Imagick::ORIENTATION_RIGHTBOTTOM:
						$image->flopImage();
						$image->rotateImage(new \ImagickPixel(), 90);
						break;
					case \Imagick::ORIENTATION_LEFTBOTTOM:
						$image->rotateImage(new \ImagickPixel(), -90);
						break;
				}

			if ($rotate) { // we only write if there is a need. Fixes #111
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				$image->writeImage($path);
			}

			$dimensions = [
				'width' => $image->getImageWidth(),
				'height' => $image->getImageHeight(),
			];

			$image->clear();
			$image->destroy();

			return $dimensions;
		} catch (ImagickException $exception) {
			Logs::error(__METHOD__, __LINE__, $exception->getMessage());

			return [false, false];
		}
	}
}
