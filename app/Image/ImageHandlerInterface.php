<?php

namespace App\Image;

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
	 * @param int $compressionQuality
	 */
	public function __construct(int $compressionQuality);

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
	 * @param MediaFile the file to write into
	 *
	 * @return StreamStat statistics about the stream
	 *
	 * @throws MediaFileOperationException
	 */
	public function save(MediaFile $file): StreamStat;

	/**
	 * Frees all internal resources.
	 *
	 * This method resets the object into a state where no image is loaded.
	 * It frees all internal resources, releases all temporary buffers and
	 * closes all streams.
	 * Any work which has not been saved by a prior call to
	 * {@link ImageHandlerInterface::save()} followed by
	 * {@link ImageHandlerInterface::close()} will be lost.
	 *
	 * @return void
	 */
	public function reset(): void;

	/**
	 * Scales the image proportionally to the designated dimensions such
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
	 * @return ImageDimension the resulting dimension
	 *
	 * @throws ImageProcessingException
	 * @throws LycheeDomainException
	 */
	public function scale(ImageDimension $dstDim): ImageDimension;

	/**
	 * Crops the image to the designated dimensions.
	 *
	 * @param ImageDimension $dstDim the designated dimensions
	 *
	 * @return void
	 *
	 * @throws ImageProcessingException
	 */
	public function crop(ImageDimension $dstDim): void;

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
