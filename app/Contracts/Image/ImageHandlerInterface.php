<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Image;

use App\DTO\ImageDimension;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

/**
 * Interface ImageHandlerInterface.
 */
interface ImageHandlerInterface
{
	/**
	 * Loads an image from the provided file.
	 *
	 * @param MediaFile $file the file to read from
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 * @throws ImageProcessingException
	 */
	public function load(MediaFile $file): void;

	/**
	 * Save the image into the provided file.
	 *
	 * @param MediaFile $file              the file to write into
	 * @param bool      $collectStatistics if true, the method returns statistics about the stream
	 *
	 * @return StreamStats|null optional statistics about the stream, if requested
	 *
	 * @throws MediaFileOperationException
	 */
	public function save(MediaFile $file, bool $collectStatistics = false): ?StreamStats;

	/**
	 * Frees all internal resources.
	 *
	 * This method resets the object into a state where no image is loaded.
	 * It frees all internal resources, releases all temporary buffers and
	 * closes all streams.
	 * Any work which has not been saved by a prior call to
	 * {@link ImageHandlerInterface::save()} will be lost.
	 *
	 * @return void
	 */
	public function reset(): void;

	/**
	 * Clones and scales the image proportionally to the designated dimensions such
	 * that the new dimension don't exceed the designated dimensions.
	 *
	 * The resulting dimension may differ from the requested dimension due
	 * to proportional scaling.
	 *
	 * Either the new width or height may be zero which means that this
	 * dimension is chosen automatically.
	 *
	 * @param ImageDimension $dstDim the designated dimensions
	 *
	 * @return ImageHandlerInterface the scaled clone
	 *
	 * @throws ImageProcessingException
	 * @throws LycheeDomainException
	 */
	public function cloneAndScale(ImageDimension $dstDim): ImageHandlerInterface;

	/**
	 * Clones and crops the image to the designated dimensions.
	 *
	 * @param ImageDimension $dstDim the designated dimensions
	 *
	 * @return ImageHandlerInterface the cropped clone
	 *
	 * @throws ImageProcessingException
	 */
	public function cloneAndCrop(ImageDimension $dstDim): ImageHandlerInterface;

	/**
	 * Rotates the imaged based on the given angle.
	 *
	 * @param int $angle
	 *
	 * @return ImageDimension the resulting dimension
	 *
	 * @throws ImageProcessingException
	 * @throws LycheeDomainException    thrown if `$angle` is out-of-bounds
	 */
	public function rotate(int $angle): ImageDimension;

	/**
	 * Returns the dimension of the image in upright orientation.
	 *
	 * The returned dimension may be swapped compared to the dimensions
	 * returned by raw EXIF data.
	 * An image may be stored rotated or mirrored and EXIF returns the raw
	 * width and height.
	 * This method returns the dimension after the photo has been put into
	 * upright orientation.
	 *
	 * @return ImageDimension the dimensions of the image
	 *
	 * @throws ImageProcessingException
	 */
	public function getDimensions(): ImageDimension;

	/**
	 * @return bool true, if an image is loaded
	 */
	public function isLoaded(): bool;
}
