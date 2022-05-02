<?php

namespace App\Image;

use App\DTO\ImageDimension;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use Safe\Exceptions\ImageException;

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
	 * TODO: Remove `resource` after `\Safe` has been migrated to PHP 8.
	 *
	 * @var \GdImage|resource|null the opaque GD handler
	 */
	private $gdImage = null;

	/** @var int the image type detected by GD upon loading */
	private int $gdImageType = 0;

	/**
	 * @throws ImageProcessingException
	 */
	public function __clone()
	{
		// Cloning of \GdImage is complicated and not 100% reliable, if the
		// original image uses transparency. :-(
		// But photos hopefully don't use transparency too often. :-)
		if ($this->gdImage !== null) {
			$dim = $this->getDimensions();

			try {
				if (imageistruecolor($this->gdImage)) {
					// If this is a true color image...
					$clone = \Safe\imagecreatetruecolor($dim->width, $dim->height);
					\Safe\imagealphablending($clone, false);
					// As we don't know if the original image has an alpha channel,
					// we must unconditionally enable transparency for the clone
					// in order to be on the safe side.
					// This seems to be a limitation of the GD library.
					// This may needlessly increase storage size by an extra
					// 8bit channel for image formats which support transparency
					// (e.g TIFF, PNG, etc.)
					// For formats which don't support transparency (e.g. JPEG),
					// this method has no effect.
					\Safe\imagesavealpha($clone, true);
					// Note the comment in the PHP doc:
					// `imagecopymerge` with pct = 100 is not identical to `imagecopy`
					// because it preserves transparency
					\Safe\imagecopymerge($clone, $this->gdImage, 0, 0, 0, 0, $dim->width, $dim->height, 100);
				} else {
					// If this is a 256 color palette image...
					$clone = \Safe\imagecreate($dim->width, $dim->height);
					imagepalettecopy($clone, $this->gdImage);
					$transColorIndex = imagecolortransparent($this->gdImage);
					if ($transColorIndex !== -1) {
						\Safe\imagefill($clone, 0, 0, $transColorIndex);
					}
					\Safe\imagecopy($clone, $this->gdImage, 0, 0, 0, 0, $dim->width, $dim->height);
				}

				$this->gdImage = $clone;
			} catch (\ErrorException $e) {
				throw new ImageProcessingException('Failed to clone image', $e);
			}
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
			$this->reset();

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

			// Determine the type of image, so that we can later save the
			// image using the same type
			list(, , $this->gdImageType) = \Safe\getimagesize(stream_get_contents($inputStream));
			\Safe\rewind($inputStream);
			if (!in_array($this->gdImageType, self::SUPPORTED_IMAGE_TYPES)) {
				$this->reset();
				throw new MediaFileUnsupportedException('Type of photo is not supported');
			}

			// Load image
			error_clear_last();
			// TODO: Replace `imagecreatefromstring` by `\Safe\imagecreatefromstring` after https://github.com/thecodingmachine/safe/issues/352 has been resolved
			$this->gdImage = imagecreatefromstring(\Safe\stream_get_contents($inputStream));
			if (!$this->gdImage) {
				throw ImageException::createFromPhpError();
			}
			\Safe\rewind($inputStream);

			// Get EXIF data to determine whether rotation is required
			error_clear_last();
			// TODO: Replace `exif_read_data` by `\Safe\exif_read_data` after https://github.com/thecodingmachine/safe/issues/215 has been resolved
			$exifData = exif_read_data($inputStream);
			if ($exifData === false) {
				throw ImageException::createFromPhpError();
			}

			// Auto-rotate image
			// TODO: Check if `exif_read_data` actually uses the key `Orientation` with a capital 'O'
			$orientation = !empty($exifData['Orientation']) ? $exifData['Orientation'] : 1;
			$this->autoRotate($orientation);
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
	public function save(MediaFile $file): StreamStat
	{
		if (!$this->gdImage) {
			new MediaFileOperationException('No image loaded');
		}
		try {
			// We write the image into a memory buffer first, because
			// we don't know if the file is a local file (or hosted elsewhere)
			// and if the file supports seekable streams
			$inMemoryBuffer = new InMemoryBuffer();

			switch ($this->gdImageType) {
				case IMAGETYPE_JPEG:
				case IMAGETYPE_JPEG2000:
					\Safe\imagejpeg($this->gdImage, $inMemoryBuffer->stream(), $this->compressionQuality);
					break;
				case IMAGETYPE_PNG:
					\Safe\imagepng($this->gdImage, $inMemoryBuffer->stream());
					break;
				case IMAGETYPE_GIF:
					\Safe\imagegif($this->gdImage, $inMemoryBuffer->stream());
					break;
				case IMAGETYPE_WEBP:
					\Safe\imagewebp($this->gdImage, $inMemoryBuffer->stream());
					break;
				default:
					assert(false, new \AssertionError('uncovered image type'));
			}

			$streamStat = $file->write($inMemoryBuffer->read(), true);
			$file->close();
			$inMemoryBuffer->close();

			return parent::applyLosslessOptimizationConditionally($file) ?: $streamStat;
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
				1, 2, 4 => 0,
				3 => -180,
				5, 6 => -90,
				7, 8 => 90,
			};

			$flip = match ($orientation) {
				1, 3, 6, 8 => 0,
				2, 7, 5 => IMG_FLIP_HORIZONTAL,
				4 => IMG_FLIP_VERTICAL,
			};

			if ($angle !== 0) {
				$this->gdImage = \Safe\imagerotate($this->gdImage, $angle, 0);
			}

			if ($flip !== 0) {
				\Safe\imageflip($this->gdImage, $flip);
			}
		} catch (\ErrorException $e) {
			throw new ImageProcessingException('Failed to auto-rotate image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function scale(ImageDimension $dstDim): ImageDimension
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

			$image = \Safe\imagecreatetruecolor($width, $height);
			$this->fastImageCopyResampled($image, $this->gdImage, 0, 0, 0, 0, $width, $height, $srcDim->width, $srcDim->height);
			$this->gdImage = $image;

			return new ImageDimension($width, $height);
		} catch (\ErrorException $e) {
			$this->reset();
			throw new ImageProcessingException('Failed to scale image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(ImageDimension $dstDim): void
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

			$image = \Safe\imagecreatetruecolor($dstDim->width, $dstDim->height);
			$this->fastImageCopyResampled($image, $this->gdImage, 0, 0, $x, $y, $dstDim->width, $dstDim->height, $width, $height);
			$this->gdImage = $image;
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
			$this->gdImage = \Safe\imagerotate($this->gdImage, -$angle, 0);

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
	 * Optional "quality" parameter (defaults is 4). Fractional values are allowed, for example 1.5. Must be greater than zero.
	 * Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
	 * 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
	 * 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
	 * 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
	 * 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
	 * 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
	 *
	 * TODO: Remove `resource` from type after `\Safe` has been migrated to PHP 8.
	 *
	 * @param \GdImage|resource $dst_image
	 * @param \GdImage|resource $src_image
	 * @param int               $dst_x
	 * @param int               $dst_y
	 * @param int               $src_x
	 * @param int               $src_y
	 * @param int               $dst_w
	 * @param int               $dst_h
	 * @param int               $src_w
	 * @param int               $src_h
	 * @param int               $quality
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
		int $quality = 4
	): void {
		try {
			if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
				$temp = \Safe\imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
				\Safe\imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
				\Safe\imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
			} else {
				\Safe\imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
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
			return new ImageDimension(\Safe\imagesx($this->gdImage), \Safe\imagesy($this->gdImage));
		} catch (\ErrorException $e) {
			throw new ImageProcessingException('Could not determine dimensions of image', $e);
		}
	}

	public function isLoaded(): bool
	{
		return $this->gdImageType !== 0 && $this->gdImage;
	}
}
