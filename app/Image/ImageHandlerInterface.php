<?php

namespace App\Image;

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
	 * TODO: Get rid of the parameters `$source` and `$destination`. See comment on the interface.
	 *
	 * @param string $source
	 * @param string $destination
	 * @param int    $newWidth
	 * @param int    $newHeight
	 * @param int    &$resWidth
	 * @param int    &$resHeight
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function scale(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight,
		int &$resWidth,
		int &$resHeight
	): void;

	/**
	 * TODO: Get rid of the parameters `$source` and `$destination`. See comment on the interface.
	 *
	 * @param string $source
	 * @param string $destination
	 * @param int    $newWidth
	 * @param int    $newHeight
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function crop(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight
	): void;

	/**
	 * Rotates and flips a photo based on its EXIF orientation.
	 *
	 * TODO: Get rid of the parameters `$source` and `$destination`. See comment on the interface.
	 *
	 * @param string $path
	 * @param int    $orientation the orientation value (1..8) as defined by EXIF specification, default is 1 (means up-right and not mirrored/flipped)
	 * @param bool   $pretend
	 *
	 * @return array{width: int, height: int} an associative array `['width' => (int), 'height' => (int)]` with the new width and height after rotation
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array;

	/**
	 * TODO: Get rid of the parameters `$source` and `$destination`. See comment on the interface.
	 *
	 * @param string  $source
	 * @param int     $angle
	 * @param ?string $destination if `null`, the image is rotated in place
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 * @throws LycheeDomainException         thrown if `$angle` is out-of-bounds
	 */
	public function rotate(string $source, int $angle, ?string $destination = null): void;
}
