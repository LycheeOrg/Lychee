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

	public function __clone()
	{
		if ($this->imImage) {
			$this->imImage = clone $this->imImage;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset(): void
	{
		$this->imImage?->clear();
		$this->imImage = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(MediaFile $file): void
	{
		try {
			if ($this->imImage) {
				$this->reset();
			}

			$originalStream = $file->read();
			$inMemoryBuffer = new InMemoryBuffer();
			if ((stream_get_meta_data($originalStream))['seekable']) {
				$inputStream = $originalStream;
			} else {
				// We make an in-memory copy of the provided stream,
				// because we must be able to seek/rewind the stream.
				// For example, a readable stream from a remote location (i.e.
				// a "download" stream) is only forward readable once.
				$inMemoryBuffer->write($originalStream);
				$inputStream = $inMemoryBuffer->read();
			}

			$this->imImage = new Imagick();
			$this->imImage->readImageFile($inputStream);
			$this->autoRotate();
		} catch (ImagickException $e) {
			throw new MediaFileOperationException('Failed to load image', $e);
		} finally {
			$inMemoryBuffer->free();
			$file->close();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(MediaFile $file): void
	{
		if (!$this->imImage) {
			new MediaFileOperationException('No image loaded');
		}
		try {
			$this->imImage->setImageCompressionQuality($this->compressionQuality);
			$profiles = $this->imImage->getImageProfiles('icc', true);
			// Remove metadata to save some bytes
			$this->imImage->stripImage();
			// Re-add  color profiles
			if (!empty($profiles)) {
				$this->imImage->profileImage('icc', $profiles['icc']);
			}

			// We write the image into a memory buffer first, because
			// we don't know if the file is a local file (or hosted elsewhere)
			// and if the file supports seekable streams
			$inMemoryBuffer = new InMemoryBuffer();
			$this->imImage->writeImageFile($inMemoryBuffer->stream());
			$file->write($inMemoryBuffer->read());
			$file->close();
			$inMemoryBuffer->free();

			parent::applyLosslessOptimizationConditionally($file);
		} catch (ImagickException $e) {
			throw new MediaFileOperationException('Failed to save image', $e);
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
				throw new ImagickException('Failed to flop image');
			}

			if ($angle !== 0 && !$this->imImage->rotateImage(new ImagickPixel(), $angle)) {
				throw new ImagickException('Failed to rotate image');
			}

			if (!$this->imImage->setImageOrientation(Imagick::ORIENTATION_TOPLEFT)) {
				throw new ImagickException('Failed to set orientation');
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
			if (!$this->imImage->scaleImage(
				$dstDim->width, $dstDim->height, ($dstDim->width !== 0 && $dstDim->height !== 0)
			)) {
				throw new ImagickException('Failed to scale image');
			}

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
			if (!$this->imImage->cropThumbnailImage($dstDim->width, $dstDim->height)) {
				throw new ImagickException('Failed to crop image');
			}
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
			if (!$this->imImage->rotateImage(new ImagickPixel(), $angle)) {
				throw new ImagickException('Failed to rotate image');
			}

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
