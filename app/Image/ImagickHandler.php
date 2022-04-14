<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;
use App\Models\Configs;
use Imagick;
use ImagickException;
use ImagickPixel;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class ImagickHandler implements ImageHandlerInterface
{
	private int $compressionQuality;

	/**
	 * Rotates a given image based on the given orientation.
	 *
	 * @param Imagick $image the image reference to rotate
	 *
	 * @return array{width: int, height: int} a dictionary of width and height of the rotated image
	 *
	 * @throws MediaFileOperationException
	 */
	private function autoRotateInternal(Imagick $image): array
	{
		try {
			$success = true;
			$orientation = $image->getImageOrientation();

			switch ($orientation) {
				case Imagick::ORIENTATION_TOPLEFT:
					// nothing to do
					break;
				case Imagick::ORIENTATION_TOPRIGHT:
					$success = $image->flopImage();
					break;
				case Imagick::ORIENTATION_BOTTOMRIGHT:
					$success = $image->rotateImage(new ImagickPixel(), 180);
					break;
				case Imagick::ORIENTATION_BOTTOMLEFT:
					$success = $image->flopImage();
					$success &= $image->rotateImage(new ImagickPixel(), 180);
					break;
				case Imagick::ORIENTATION_LEFTTOP:
					$success = $image->flopImage();
					$success &= $image->rotateImage(new ImagickPixel(), -90);
					break;
				case Imagick::ORIENTATION_RIGHTTOP:
					$success = $image->rotateImage(new ImagickPixel(), 90);
					break;
				case Imagick::ORIENTATION_RIGHTBOTTOM:
					$success = $image->flopImage();
					$success &= $image->rotateImage(new ImagickPixel(), 90);
					break;
				case Imagick::ORIENTATION_LEFTBOTTOM:
					$success = $image->rotateImage(new ImagickPixel(), -90);
					break;
			}

			$success &= $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);

			if (!$success) {
				throw new MediaFileOperationException('Failed to rotate image');
			}

			return [
				'width' => $image->getImageWidth(),
				'height' => $image->getImageHeight(),
			];
		} catch (ImagickException $exception) {
			throw new MediaFileOperationException('Failed to rotate image', $exception);
		}
	}

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
	): void {
		try {
			// Read image
			$image = new Imagick();
			$image->readImage($source);
			// the image may need to be rotated prior to scaling
			$this->autoRotateInternal($image);

			$image->setImageCompressionQuality($this->compressionQuality);

			$profiles = $image->getImageProfiles('icc', true);

			$image->scaleImage($newWidth, $newHeight, ($newWidth != 0 && $newHeight != 0));

			// Remove metadata to save some bytes
			$image->stripImage();

			if (!empty($profiles)) {
				$image->profileImage('icc', $profiles['icc']);
			}

			$image->writeImage($destination);
			$resWidth = $image->getImageWidth();
			$resHeight = $image->getImageHeight();
			$image->clear();
			$image->destroy();

			// Optimize image
			if (Configs::get_value('lossless_optimization', '0') == '1') {
				ImageOptimizer::optimize($destination);
			}
		} catch (ImagickException $exception) {
			throw new MediaFileOperationException('Failed to scale image ' . $source, $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight
	): void {
		try {
			$image = new Imagick();
			$image->readImage($source);
			// the image may need to be rotated prior to cropping
			$this->autoRotateInternal($image);

			$image->setImageCompressionQuality($this->compressionQuality);

			$profiles = $image->getImageProfiles('icc', true);

			$image->cropThumbnailImage($newWidth, $newHeight);

			// Remove metadata to save some bytes
			$image->stripImage();

			if (!empty($profiles)) {
				$image->profileImage('icc', $profiles['icc']);
			}

			$image->writeImage($destination);
			$image->clear();
			$image->destroy();

			// Optimize image
			if (Configs::get_value('lossless_optimization', '0') == '1') {
				ImageOptimizer::optimize($destination);
			}
		} catch (ImagickException $exception) {
			throw new MediaFileOperationException('Failed to crop image ' . $source, $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array
	{
		try {
			$image = new Imagick();
			$image->readImage($path);

			$rotate = $image->getImageOrientation() !== Imagick::ORIENTATION_TOPLEFT;

			$dimensions = $this->autoRotateInternal($image);

			if ($rotate && !$pretend) {
				$image->writeImage($path);
			}

			$image->clear();
			$image->destroy();

			return $dimensions;
		} catch (ImagickException $exception) {
			throw new MediaFileOperationException('Failed to rotate image ' . $path, $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(string $source, int $angle, string $destination = null): void
	{
		try {
			$image = new Imagick();
			$image->readImage($source);
			// the image may need to be rotated upright prior to the requested rotation
			$this->autoRotateInternal($image);
			$image->rotateImage(new ImagickPixel(), $angle);
			$image->writeImage($destination);
			$image->clear();
			$image->destroy();
		} catch (ImagickException $exception) {
			throw new MediaFileOperationException('Failed to rotate image ' . $source, $exception);
		}
	}
}
