<?php

namespace App\Image;

use App\DTO\ImageDimension;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

/**
 * Interface ImageHandlerInterface.
 *
 * TODO: If we ever plan to support other than the local filesystem this interface must be heavily refactored.
 *
 * In particular, the interface must not use strings which represent paths of
 * image file, but must entirely work on streams or resources in PHP
 * terminology.
 * These streams are provided by Flysystem and may represent local or
 * remote files (it doesn't really matter).
 *
 * This is the idea:
 * The interface should represent a image (not an image handler).
 * The interface should provide a `read`-method which reads from a stream
 * and creates the image in memory.
 * All methods which are currently defined by this interface operate on this
 * memory representation.
 * In particular, the methods don't receive any paths.
 * The interface should provide a `write`-method which write the current
 * in-memory image to the stream.
 * This works for both child classes {@link GdHandler} and
 * {@link ImagickHandler}.
 * Both libraries provide classes and methods to read from/write to streams
 * in an object-oriented fashion.
 */
interface ImageHandlerInterface
{
	/**
	 * @param int $compressionQuality
	 */
	public function __construct(int $compressionQuality);

	/**
	 * Loads an image from the provided stream.
	 *
	 * @param resource $stream the stream to read from
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 * @throws ImageProcessingException
	 */
	public function load($stream): void;

	/**
	 * Provides a (readable) stream for saving into a file.
	 *
	 * This might appear a little counter-intuitive.
	 * Typically, you would expect such a method to take a stream and then
	 * write into that stream.
	 * But this is not how Flysystem works.
	 * Flysystem expects to get a (readable) stream which can then be streamed
	 * into a file and these methods are supposed to be compatible with
	 * Flysystem.
	 *
	 * The caller must call {@link ImageHandlerInterface::close()} after
	 * the returned stream has been used to free the resources of the stream.
	 *
	 * TODO: Find a better name for this method which intuitively reflects what really happens
	 *
	 * @return resource a (readable) stream whose content can be written into
	 *                  another stream, e.g. a file stream
	 *
	 * @throws MediaFileOperationException
	 */
	public function save();

	/**
	 * Closes the readable stream previously returned by {@link ImageHandlerInterface::save()}.
	 *
	 * It is safe to call this method, even if no stream has been opened.
	 * In this case, the method is a silent no-op.
	 * This method neither destroys the in-memory representation of a loaded
	 * image.
	 * I.e., no work will be lost by calling this method.
	 * See {@link ImageHandlerInterface::reset()} for that.
	 *
	 * @return void
	 */
	public function close(): void;

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
}
