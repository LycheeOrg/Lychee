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
use App\Exceptions\Handler;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\Files\InMemoryBuffer;
use Safe\Exceptions\ImageException;
use function Safe\imagecopyresampled;
use function Safe\imagecopyresized;
use function Safe\imagecreatefromstring;
use function Safe\imagecreatetruecolor;
use function Safe\imagecrop;
use function Safe\imageflip;
use function Safe\imagegif;
use function Safe\imagejpeg;
use function Safe\imagepng;
use function Safe\imagesx;
use function Safe\imagesy;
use function Safe\imagewebp;
use function Safe\rewind;
use function Safe\stream_get_contents;

class GdHandler extends BaseImageHandler
{
	public const SUPPORTED_IMAGE_TYPES = [
		IMAGETYPE_JPEG,
		IMAGETYPE_JPEG2000,
		IMAGETYPE_PNG,
		IMAGETYPE_GIF,
		IMAGETYPE_WEBP,
	];

	/**
	 * @var \GdImage|null the opaque GD handler
	 */
	private ?\GdImage $gdImage = null;

	/** @var int the image type detected by GD upon loading */
	private int $gdImageType = 0;

	/**
	 * @throws ImageProcessingException
	 */
	public function __clone()
	{
		try {
			// \GdImage is an uncloneable object.
			// Moreover, the library does not provide a method to make
			// a proper copy of an \GdImage which preserves all attributes.
			// Making a reliable copy of a \GdImage is a hassle.
			// The main problem is that a \GdImage instance can represent one
			// out of four types of images:
			//
			//  a) a true color image without alpha channel
			//  b) a true color image with alpha channel
			//  c) a palette image without a palette entry for transparency
			//  d) a palette image with a palette entry for transparency
			//
			// Programmatically, the former two are created via
			// `imagecreatetruecolor`, the latter two are created via
			// `imagecreate`.
			// Methods which take two `\GdImage` arguments - a source and
			// a destination - such as `imagecopy` behave unreliably, if
			// the provided source and destination are of different type.
			// Typically, one ends up with color distortions or
			// transparency turning into black or white.
			// For palette images one has to ensure that the palette is
			// copied from the source to the destination via
			// `imagepalettecopy` before the actual image is copied.
			// However, `imagepalettecopy` must not be used on a true color
			// image or funny things start to happen.
			// Unfortunately, if one does not create a `\GdImage` with
			// `imagecreatetruecolor` or `imagecreate` but loads an image
			// from a file via `imagecreatefrom...` the type of the
			// returned `\GdImage` is unpredictable.
			// For example a PNG or TIFF image can either be a palette image
			// or a true color image.
			// TLTR: Making a good, deep copy of a `\GdImage` is a nightmare.
			//
			// The problem is discussed in https://stackoverflow.com/q/12605768/2690527.
			// The accepted answer https://stackoverflow.com/a/12606659/2690527
			// is not good, because it simply assumes that one has case a)
			// (true color without alpha channel).
			// The answer https://stackoverflow.com/a/14690598/2690527 is
			// better even though it bears some easy-to-fix programming errors
			// like undefined variables.
			// However, it has two drawbacks:
			//  - In order to be on the safe side, the copied image always
			//    has a transparency channel for true color images even if
			//    the image does not use transparency at all.
			//  - Due to its usage of `imagealphablending` it turns out to be
			//    incredibly slow, even slower than just re-loading the
			//    GD image from disk and decoding the image stream.
			//
			// The best solution seems to exploit `imagecrop`.
			// Opposed to other methods, `imagecrop` does not take two
			// arguments - the source and the destination - but creates
			// a deep copy internally and **returns** the result.
			// Hence, we use `imagecrop` without actually cropping anything
			// to get a deep copy.
			// With respect to efficiency `imagecrop` does not seem to be as
			// efficient as a true clone method could be, but it is not worse
			// than re-loading the image from disk.
			if ($this->gdImage !== null) {
				$dim = $this->getDimensions();
				// We exploit `imagecrop` to get a deep copy of the image;
				// see long explanation above
				$this->gdImage = imagecrop($this->gdImage, ['x' => 0, 'y' => 0, 'width' => $dim->width, 'height' => $dim->height]);
			}
		} catch (\ErrorException $e) {
			throw new ImageProcessingException('Failed to clone image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset(): void
	{
		$this->gdImage = null;
		$this->gdImageType = 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(MediaFile $file): void
	{
		try {
			$inMemoryBuffer = new InMemoryBuffer();
			$this->reset();

			$originalStream = $file->read();
			if (stream_get_meta_data($originalStream)['seekable']) {
				$inputStream = $originalStream;
			} else {
				// We make an in-memory copy of the provided stream,
				// because we must be able to seek/rewind the stream.
				// For example, a readable stream from a remote location (i.e.
				// a "download" stream) is only forward readable once.
				$inMemoryBuffer->write($originalStream);
				$inputStream = $inMemoryBuffer->read();
			}

			$imgBinary = stream_get_contents($inputStream);
			rewind($inputStream);

			// Determine the type of image, so that we can later save the
			// image using the same type
			error_clear_last();
			$gdImgStat = getimagesizefromstring($imgBinary);
			if ($gdImgStat === false) {
				throw ImageException::createFromPhpError();
			} else {
				$this->gdImageType = $gdImgStat[2];
			}
			if (!in_array($this->gdImageType, self::SUPPORTED_IMAGE_TYPES, true)) {
				$this->reset();
				throw new MediaFileUnsupportedException('Type of photo is not supported');
			}

			// Load image
			error_clear_last();
			/** @var \GdImage $img */
			$img = imagecreatefromstring($imgBinary);
			$this->gdImage = $img;

			// Get EXIF data to determine whether rotation is required
			// `exif_read_data` only supports JPEGs
			if (in_array($this->gdImageType, [IMAGETYPE_JPEG, IMAGETYPE_JPEG2000], true)) {
				error_clear_last();
				// `exif_read_data` raises E_WARNING/E_NOTICE errors for unsupported
				// tags, which could result in exceptions being thrown, even though
				// the function would otherwise succeed to return valid tags.
				// We explicitly disable this undesirable behavior and use
				// the silence operator to suck out as much EXIF data as
				// possible even if some EXIF tags are unsupported.
				// As this way, `exif_read_data` does not throw any exception
				// at all (even for catastrophic errors), we need to check
				// manually, if we need to throw an exception.
				// TODO: Replace `exif_read_data` by `\Safe\exif_read_data` after https://github.com/thecodingmachine/safe/issues/215 has been resolved
				// @phpstan-ignore-next-line
				$exifData = @exif_read_data($inputStream);
				$phpError = error_get_last();
				if ($exifData === false || $phpError !== null) {
					$exception = ImageException::createFromPhpError();
					if ($exifData === false) {
						// something went wrong catastrophically, throw the
						// exception as `exif_read_data` would have done without @
						throw $exception;
					} else {
						// exif_read_data() returned an array and has been able
						// to extract some useful data, but still reported a
						// warning; don't throw the exception, but log it and
						// proceed
						Handler::reportSafely($exception);
					}
				}

				// Auto-rotate image
				$orientation = array_key_exists('Orientation', $exifData) && is_numeric($exifData['Orientation']) ? (int) $exifData['Orientation'] : 1;
				$this->autoRotate($orientation);
			}
		} catch (\ErrorException $e) {
			$this->reset();
			throw new MediaFileOperationException('Failed to load image', $e);
		} finally {
			$inMemoryBuffer->close();
			$file->close();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(MediaFile $file, bool $collectStatistics = false): ?StreamStats
	{
		if ($this->gdImage === null) {
			throw new MediaFileOperationException('No image loaded');
		}
		try {
			// We write the image into a memory buffer first, because
			// we don't know if the file is a local file (or hosted elsewhere)
			// and if the file supports seekable streams
			$inMemoryBuffer = new InMemoryBuffer();

			match ($this->gdImageType) {
				IMAGETYPE_JPEG,
				IMAGETYPE_JPEG2000 => imagejpeg($this->gdImage, $inMemoryBuffer->stream(), $this->compressionQuality),
				IMAGETYPE_PNG => imagepng($this->gdImage, $inMemoryBuffer->stream()),
				IMAGETYPE_GIF => imagegif($this->gdImage, $inMemoryBuffer->stream()),
				IMAGETYPE_WEBP => imagewebp($this->gdImage, $inMemoryBuffer->stream()),
				default => throw new \AssertionError('uncovered image type'),
			};

			$streamStat = $file->write($inMemoryBuffer->read(), $collectStatistics);
			$file->close();
			$inMemoryBuffer->close();

			return parent::applyLosslessOptimizationConditionally($file) ?? $streamStat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to save image', $e);
		}
	}

	/**
	 * Rotates and flips a photo based on the designated EXIF orientation.
	 *
	 * @param int $orientation the orientation value (1..8) as defined by EXIF specification, default is 1 (means up-right and not mirrored/flipped)
	 *
	 * @return void
	 *
	 * @throws ImageProcessingException
	 */
	private function autoRotate(int $orientation): void
	{
		try {
			$angle = match ($orientation) {
				0, 1, 2, 4 => 0,
				3 => -180,
				5, 6 => -90,
				7, 8 => 90,
				default => throw new ImageProcessingException('Image orientation out of range'),
			};

			$flip = match ($orientation) {
				0, 1, 3, 6, 8 => 0,
				2, 7, 5 => IMG_FLIP_HORIZONTAL,
				4 => IMG_FLIP_VERTICAL,
				default => throw new ImageProcessingException('Image orientation out of range'),
			};

			if ($angle !== 0) {
				$this->gdImage = $this->imagerotate($this->gdImage, $angle, 0);
			}

			if ($flip !== 0) {
				imageflip($this->gdImage, $flip);
			}
		} catch (\ErrorException $e) {
			throw new ImageProcessingException('Failed to auto-rotate image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function cloneAndScale(ImageDimension $dstDim): ImageHandlerInterface
	{
		try {
			$srcDim = $this->getDimensions();

			if ($dstDim->width === 0 && $dstDim->height !== 0) {
				$scale = $dstDim->height / $srcDim->height;
			} elseif ($dstDim->width !== 0 && $dstDim->height === 0) {
				$scale = $dstDim->width / $srcDim->width;
			} elseif ($dstDim->width !== 0 && $dstDim->height !== 0) {
				$scale = min($dstDim->width / $srcDim->width, $dstDim->height / $srcDim->height);
			} else {
				throw new LycheeDomainException('Width and height must not be zero simultaneously');
			}

			$width = (int) round($scale * $srcDim->width);
			$height = (int) round($scale * $srcDim->height);

			$clonedGdImage = imagecreatetruecolor($width, $height);
			$this->fastImageCopyResampled($clonedGdImage, $this->gdImage, 0, 0, 0, 0, $width, $height, $srcDim->width, $srcDim->height);

			$clone = new self();
			$clone->compressionQuality = $this->compressionQuality;
			$clone->gdImage = $clonedGdImage;
			$clone->gdImageType = $this->gdImageType;

			return $clone;
		} catch (\ErrorException $e) {
			$this->reset();
			throw new ImageProcessingException('Failed to scale image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function cloneAndCrop(ImageDimension $dstDim): ImageHandlerInterface
	{
		try {
			$srcDim = $this->getDimensions();

			$srcWHRatio = $srcDim->width / $srcDim->height;
			$dstWHRatio = $dstDim->width / $dstDim->height;

			if ($dstWHRatio > $srcWHRatio) {
				// The designated ratio is wider than the source ratio
				// Hence, we must crop off the height
				$width = $srcDim->width;
				$x = 0;
				$height = (int) round($srcDim->width / $dstWHRatio);
				$y = (int) round(($srcDim->height - $height) / 2);
			} else {
				// Inverse case: we must crop off the width
				$width = (int) round($srcDim->height * $dstWHRatio);
				$x = (int) round(($srcDim->width - $width) / 2);
				$height = $srcDim->height;
				$y = 0;
			}

			$clonedGdImage = imagecreatetruecolor($dstDim->width, $dstDim->height);
			$this->fastImageCopyResampled($clonedGdImage, $this->gdImage, 0, 0, $x, $y, $dstDim->width, $dstDim->height, $width, $height);

			$clone = new self();
			$clone->compressionQuality = $this->compressionQuality;
			$clone->gdImage = $clonedGdImage;
			$clone->gdImageType = $this->gdImageType;

			return $clone;
		} catch (\ErrorException $e) {
			$this->reset();
			throw new ImageProcessingException('Failed to crop image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(int $angle): ImageDimension
	{
		try {
			$this->gdImage = $this->imagerotate($this->gdImage, -$angle, 0);

			return $this->getDimensions();
		} catch (\ErrorException $e) {
			$this->reset();
			throw new ImageProcessingException('Failed to rotate image', $e);
		}
	}

	/**
	 * Plug-and-Play fastImageCopyResampled function replaces much slower imagecopyresampled.
	 * Just include this function and change all "imagecopyresampled" references to "fastImageCopyResampled".
	 * Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
	 * Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
	 *
	 * Optional "quality" parameter (default is 4). Fractional values are allowed, for example 1.5. Must be greater than zero.
	 * Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
	 * 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
	 * 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
	 * 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
	 * 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
	 * 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
	 *
	 * @param \GdImage $dst_image
	 * @param \GdImage $src_image
	 * @param int      $dst_x
	 * @param int      $dst_y
	 * @param int      $src_x
	 * @param int      $src_y
	 * @param int      $dst_w
	 * @param int      $dst_h
	 * @param int      $src_w
	 * @param int      $src_h
	 * @param int      $quality
	 *
	 * @return void
	 *
	 * @throws ImageProcessingException
	 */
	private function fastImageCopyResampled(
		$dst_image,
		$src_image,
		int $dst_x,
		int $dst_y,
		int $src_x,
		int $src_y,
		int $dst_w,
		int $dst_h,
		int $src_w,
		int $src_h,
		int $quality = 4,
	): void {
		try {
			if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
				$temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
				imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
				imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
			} else {
				imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
			}
		} catch (\ErrorException $e) {
			throw new ImageProcessingException('Could not resample image', $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDimensions(): ImageDimension
	{
		try {
			return new ImageDimension(imagesx($this->gdImage), imagesy($this->gdImage));
		} catch (\ErrorException $e) {
			throw new ImageProcessingException('Could not determine dimensions of image', $e);
		}
	}

	public function isLoaded(): bool
	{
		return $this->gdImageType !== 0 && $this->gdImage !== null;
	}

	/**
	 * CORRECTED from Safe/imagerotate.
	 *
	 * Rotates the image image using the given
	 * angle in degrees.
	 *
	 * The center of rotation is the center of the image, and the rotated
	 * image may have different dimensions than the original image.
	 *
	 * @param \GdImage $image            a GdImage object, returned by one of the image creation functions,
	 *                                   such as imagecreatetruecolor
	 * @param float    $angle            Rotation angle, in degrees. The rotation angle is interpreted as the
	 *                                   number of degrees to rotate the image anticlockwise.
	 * @param int      $background_color Specifies the color of the uncovered zone after the rotation
	 *
	 * @return \GdImage returns an image object for the rotated image
	 *
	 * @throws ImageException
	 */
	private function imagerotate($image, float $angle, int $background_color)
	{
		error_clear_last();
		// @phpstan-ignore-next-line
		$safeResult = \imagerotate($image, $angle, $background_color);
		if ($safeResult === false) {
			throw ImageException::createFromPhpError();
		}

		return $safeResult;
	}
}
