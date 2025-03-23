<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Handlers;

use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Image\MediaFile;
use App\Contracts\Image\StreamStats;
use App\DTO\ImageDimension;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\InMemoryBuffer;
use Imagick;

class ImagickHandler extends BaseImageHandler
{
	/** @var \Imagick|null the internal Imagick image */
	private ?\Imagick $im_image = null;

	public function __clone()
	{
		if ($this->im_image !== null) {
			$this->im_image = clone $this->im_image;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset(): void
	{
		$this->im_image?->clear();
		$this->im_image = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(MediaFile $file): void
	{
		try {
			$in_memory_buffer = null;

			$this->reset();

			$original_stream = $file->read();
			if (stream_get_meta_data($original_stream)['seekable']) {
				$input_stream = $original_stream;
			} else {
				// We make an in-memory copy of the provided stream,
				// because we must be able to seek/rewind the stream.
				// For example, a readable stream from a remote location (i.e.
				// a "download" stream) is only forward readable once.
				$in_memory_buffer = new InMemoryBuffer();
				$in_memory_buffer->write($original_stream);
				$input_stream = $in_memory_buffer->read();
			}

			$this->im_image = new \Imagick();
			$this->im_image->readImageFile($input_stream);
			$this->autoRotate();
		} catch (\ImagickException $e) {
			throw new MediaFileOperationException('Failed to load image', $e);
		} finally {
			$in_memory_buffer?->close();
			$file->close();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(MediaFile $file, bool $collect_statistics = false): ?StreamStats
	{
		if ($this->im_image === null) {
			throw new MediaFileOperationException('No image loaded');
		}
		try {
			$this->im_image->setImageCompressionQuality($this->compression_quality);
			$profiles = $this->im_image->getImageProfiles('icc', true);
			// Remove metadata to save some bytes
			$this->im_image->stripImage();
			// Re-add  color profiles
			if (array_key_exists('icc', $profiles)) {
				$this->im_image->profileImage('icc', $profiles['icc']);
			}

			// We write the image into a memory buffer first, because
			// we don't know if the file is a local file (or hosted elsewhere)
			// and if the file supports seekable streams
			$in_memory_buffer = new InMemoryBuffer();
			$this->im_image->writeImageFile($in_memory_buffer->stream(), ltrim($file->getExtension(), '.'));
			$stream_stat = $file->write($in_memory_buffer->read(), $collect_statistics);
			$file->close();
			$in_memory_buffer->close();

			return parent::applyLosslessOptimizationConditionally($file, $collect_statistics) ?? $stream_stat;
		} catch (\ImagickException $e) {
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
			$orientation = $this->im_image->getImageOrientation();

			$needs_flop = match ($orientation) {
				\Imagick::ORIENTATION_TOPRIGHT, \Imagick::ORIENTATION_BOTTOMLEFT, \Imagick::ORIENTATION_LEFTTOP, \Imagick::ORIENTATION_RIGHTBOTTOM => true,
				\Imagick::ORIENTATION_TOPLEFT, \Imagick::ORIENTATION_BOTTOMRIGHT, \Imagick::ORIENTATION_RIGHTTOP, \Imagick::ORIENTATION_LEFTBOTTOM, \Imagick::ORIENTATION_UNDEFINED => false,
			};

			$angle = match ($orientation) {
				\Imagick::ORIENTATION_TOPLEFT, \Imagick::ORIENTATION_TOPRIGHT, \Imagick::ORIENTATION_UNDEFINED => 0,
				\Imagick::ORIENTATION_BOTTOMRIGHT, \Imagick::ORIENTATION_BOTTOMLEFT => 180,
				\Imagick::ORIENTATION_LEFTTOP, \Imagick::ORIENTATION_LEFTBOTTOM => -90,
				\Imagick::ORIENTATION_RIGHTTOP, \Imagick::ORIENTATION_RIGHTBOTTOM => 90,
			};

			if ($needs_flop && !$this->im_image->flopImage()) {
				throw new \ImagickException('Failed to flop image');
			}

			if ($angle !== 0 && !$this->im_image->rotateImage(new \ImagickPixel(), $angle)) {
				throw new \ImagickException('Failed to rotate image');
			}

			if (!$this->im_image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT)) {
				throw new \ImagickException('Failed to set orientation');
			}
		} catch (\ImagickException $exception) {
			throw new ImageProcessingException('Failed to auto-rotate image', $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function cloneAndScale(ImageDimension $dst_dim): ImageHandlerInterface
	{
		try {
			$clone = clone $this;
			if (!$clone->im_image->scaleImage(
				$dst_dim->width, $dst_dim->height, $dst_dim->width !== 0 && $dst_dim->height !== 0
			)) {
				throw new \ImagickException('Failed to scale image');
			}

			return $clone;
		} catch (\ImagickException $e) {
			throw new ImageProcessingException('Failed to scale image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function cloneAndCrop(ImageDimension $dst_dim): ImageHandlerInterface
	{
		try {
			$clone = clone $this;
			if (!$clone->im_image->cropThumbnailImage($dst_dim->width, $dst_dim->height)) {
				throw new \ImagickException('Failed to crop image');
			}

			return $clone;
		} catch (\ImagickException $e) {
			throw new ImageProcessingException('Failed to crop image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(int $angle): ImageDimension
	{
		try {
			if (!$this->im_image->rotateImage(new \ImagickPixel(), $angle)) {
				throw new \ImagickException('Failed to rotate image');
			}

			return $this->getDimensions();
		} catch (\ImagickException $e) {
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
				$this->im_image->getImageWidth(),
				$this->im_image->getImageHeight(),
			);
		} catch (\ImagickException $e) {
			throw new ImageProcessingException('Could not determine dimensions of image', $e);
		}
	}

	public function isLoaded(): bool
	{
		return $this->im_image !== null;
	}
}
