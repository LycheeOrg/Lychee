<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace App\Image;

use App\DTO\ImageDimension;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\MediaFileOperationException;
use Imagick;
use ImagickException;
use ImagickPixel;

class ImagickHandler extends BaseImageHandler
{
	/** @var Imagick|null the internal Imagick image */
	private ?Imagick $imImage = null;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(int $compressionQuality)
	{
		parent::__construct($compressionQuality);
		$this->reset();
	}

	public function __clone()
	{
		parent::__clone();
		if ($this->imImage) {
			$this->imImage = clone $this->imImage;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset(): void
	{
		parent::reset();
		$this->imImage?->clear();
		$this->imImage = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load($stream): void
	{
		if ($this->imImage) {
			throw new MediaFileOperationException('Another image is already loaded');
		}

		// We first copy the provided stream into an in-memory buffer,
		// because we must be able to seek/rewind the stream, and we do
		// not know if the provided stream supports that.
		// For example, a readable stream from a remote location (i.e.
		// a "download" stream) is only forward readable once.
		$this->createBuffer($stream);

		try {
			$this->imImage = new Imagick();
			$this->imImage->readImageFile($this->bufferStream);
			$this->close();
			$this->autoRotate();
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Could not load image', $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save()
	{
		if (!$this->imImage) {
			new MediaFileOperationException('No image loaded');
		}
		$this->createBuffer();
		try {
			$this->imImage->setImageCompressionQuality($this->compressionQuality);
			$profiles = $this->imImage->getImageProfiles('icc', true);
			// Remove metadata to save some bytes
			$this->imImage->stripImage();
			// Re-add  color profiles
			if (!empty($profiles)) {
				$this->imImage->profileImage('icc', $profiles['icc']);
			}
			$this->imImage->writeImageFile($this->bufferStream);
			rewind($this->bufferStream);

			// TODO: Re-enable image optimization again after migration to streams
			// Optimize image
			/* if (Configs::get_value('lossless_optimization', '0') == '1') {
				ImageOptimizer::optimize($destination);
			}*/

			return $this->bufferStream;
		} catch (ImagickException $e) {
			throw new MediaFileOperationException('Failed to write image', $e);
		}
	}

	/**
	 * Rotates the image such that it is oriented in upright direction;.
	 *
	 * @return void
	 *
	 * @throws ImageProcessingException
	 */
	private function autoRotate(): void
	{
		try {
			$orientation = $this->imImage->getImageOrientation();

			$needsFlop = match ($orientation) {
				Imagick::ORIENTATION_TOPRIGHT, Imagick::ORIENTATION_BOTTOMLEFT, Imagick::ORIENTATION_LEFTTOP, Imagick::ORIENTATION_RIGHTBOTTOM => true,
				Imagick::ORIENTATION_TOPLEFT, Imagick::ORIENTATION_BOTTOMRIGHT, Imagick::ORIENTATION_RIGHTTOP, Imagick::ORIENTATION_LEFTBOTTOM => false,
			};

			$angle = match ($orientation) {
				Imagick::ORIENTATION_TOPLEFT, Imagick::ORIENTATION_TOPRIGHT => 0,
				Imagick::ORIENTATION_BOTTOMRIGHT, Imagick::ORIENTATION_BOTTOMLEFT => 180,
				Imagick::ORIENTATION_LEFTTOP, Imagick::ORIENTATION_LEFTBOTTOM => -90,
				Imagick::ORIENTATION_RIGHTTOP, Imagick::ORIENTATION_RIGHTBOTTOM => 90,
			};

			if ($needsFlop && !$this->imImage->flopImage()) {
				throw new ImageProcessingException('Failed to flop image');
			}

			if ($angle !== 0 && !$this->imImage->rotateImage(new ImagickPixel(), $angle)) {
				throw new ImageProcessingException('Failed to rotate image');
			}

			if (!$this->imImage->setImageOrientation(Imagick::ORIENTATION_TOPLEFT)) {
				throw new ImageProcessingException('Failed to set orientation');
			}
		} catch (ImagickException $exception) {
			throw new ImageProcessingException('Failed to auto-rotate image', $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function scale(ImageDimension $dstDim): ImageDimension
	{
		try {
			$this->imImage->scaleImage(
				$dstDim->width, $dstDim->height, ($dstDim->width !== 0 && $dstDim->height !== 0)
			);

			return $this->getDimensions();
		} catch (ImagickException $e) {
			throw new ImageProcessingException('Failed to scale image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(ImageDimension $dstDim): void
	{
		try {
			$this->imImage->cropThumbnailImage($dstDim->width, $dstDim->height);
		} catch (ImagickException $e) {
			throw new ImageProcessingException('Failed to crop image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(int $angle): ImageDimension
	{
		try {
			$this->imImage->rotateImage(new ImagickPixel(), $angle);

			return $this->getDimensions();
		} catch (ImagickException $e) {
			throw new ImageProcessingException('Failed to rotate image', $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDimensions(): ImageDimension
	{
		try {
			return new ImageDimension(
				$this->imImage->getImageWidth(),
				$this->imImage->getImageHeight(),
			);
		} catch (ImagickException $e) {
			throw new ImageProcessingException('Could not determine dimensions of image', $e);
		}
	}
}
