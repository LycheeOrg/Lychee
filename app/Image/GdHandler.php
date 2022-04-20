<?php

namespace App\Image;

use App\DTO\ImageDimension;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

class GdHandler extends BaseImageHandler
{
	public const SUPPORTED_IMAGE_TYPES = [
		IMAGETYPE_JPEG,
		IMAGETYPE_JPEG2000,
		IMAGETYPE_PNG,
		IMAGETYPE_GIF,
		IMAGETYPE_WEBP,
	];

	/** @var \GdImage|null the opaque GD handler */
	private ?\GdImage $gdImage = null;

	/** @var int the image type detected by GD upon loading */
	private int $gdImageType = 0;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(int $compressionQuality)
	{
		parent::__construct($compressionQuality);
		$this->reset();
	}

	/**
	 * @throws ImageProcessingException
	 */
	public function __clone()
	{
		parent::__clone();

		// Cloning of \GdImage is complicated and not 100% reliable, if the
		// original image uses transparency. :-(
		// But photos hopefully don't use transparency too often. :-)
		if ($this->gdImage !== null) {
			$dim = $this->getDimensions();

			if (imageistruecolor($this->gdImage)) {
				// If this is a true color image...
				$clone = imagecreatetruecolor($dim->width, $dim->height);
				if (!$clone) {
					throw new ImageProcessingException('imagecreatetruecolor failed');
				}
				if (!imagealphablending($clone, false)) {
					throw new ImageProcessingException('imagealphablending failed');
				}
				// As we don't know if the original image has an alpha channel,
				// we must unconditionally enable transparency for the clone
				// in order to be on the safe side.
				// This seems to be a limitation of the GD library.
				// This may needlessly increase storage size by an extra
				// 8bit channel for image formats which support transparency
				// (e.g TIFF, PNG, etc.)
				// For formats which don't support transparency (e.g. JPEG),
				// this method has no effect.
				if (!imagesavealpha($clone, true)) {
					throw new ImageProcessingException('imagesavealpha failed');
				}
				// Note the comment in the PHP doc:
				// `imagecopymerge` with pct = 100 is not identical to `imagecopy`
				// because it preserves transparency
				if (!imagecopymerge($clone, $this->gdImage, 0, 0, 0, 0, $dim->width, $dim->height, 100)) {
					throw new ImageProcessingException('imagecopymerge failed');
				}
			} else {
				// If this is a 256 color palette image...
				$clone = imagecreate($dim->width, $dim->height);
				if (!$clone) {
					throw new ImageProcessingException('imagecreate failed');
				}
				imagepalettecopy($clone, $this->gdImage);
				$transColorIndex = imagecolortransparent($this->gdImage);
				if ($transColorIndex !== -1) {
					if (!imagefill($clone, 0, 0, $transColorIndex)) {
						throw new ImageProcessingException('imagefill failed');
					}
				}
				if (!imagecopy($clone, $this->gdImage, 0, 0, 0, 0, $dim->width, $dim->height)) {
					throw new ImageProcessingException('imagecopy failed');
				}
			}

			$this->gdImage = $clone;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset(): void
	{
		parent::reset();
		$this->gdImage = null;
		$this->gdImageType = 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load($stream): void
	{
		if ($this->gdImage) {
			throw new MediaFileOperationException('Another image is already loaded');
		}

		// We first copy the provided stream into an in-memory buffer,
		// because we must be able to seek/rewind the stream, and we do
		// not know if the provided stream supports that.
		// For example, a readable stream from a remote location (i.e.
		// a "download" stream) is only forward readable once.
		$this->createBuffer($stream);

		// Determine the type of image, so that we can later save the
		// image using the same type
		try {
			list(, , $this->gdImageType) = getimagesize(stream_get_contents($this->bufferStream));
			rewind($this->bufferStream);
		} catch (\Throwable $e) {
			$this->reset();
			throw new MediaFileOperationException('Could not determine type of image', $e);
		}
		if (!in_array($this->gdImageType, self::SUPPORTED_IMAGE_TYPES)) {
			$this->reset();
			throw new MediaFileUnsupportedException('Type of photo is not supported');
		}

		// Load image
		try {
			$this->gdImage = imagecreatefromstring(stream_get_contents($stream));
			if (!$this->gdImage) {
				throw new MediaFileOperationException('Could not read input stream');
			}
			rewind($this->bufferStream);

			// Get EXIF data to determine whether rotation is required
			// TODO: We should use PHPexif here, after is also has support for streams
			$exifData = exif_read_data($this->bufferStream);
			if ($exifData === false) {
				throw new MediaFileOperationException('Could not read EXIF data');
			}
			$this->close();

			// Auto-rotate image
			// TODO: Check if `exif_read_data` actually uses the key `Orientation` with a capital 'O'
			$orientation = !empty($exifData['Orientation']) ? $exifData['Orientation'] : 1;
			$this->autoRotate($orientation);
		} catch (\Throwable $e) {
			$this->reset();
			throw new MediaFileOperationException('Could not load image', $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save()
	{
		if (!$this->gdImage) {
			new MediaFileOperationException('No image loaded');
		}
		$this->createBuffer();
		try {
			$success = match ($this->gdImageType) {
				IMAGETYPE_JPEG, IMAGETYPE_JPEG2000 => imagejpeg($this->gdImage, $this->bufferStream, $this->compressionQuality),
				IMAGETYPE_PNG => imagepng($this->gdImage, $this->bufferStream),
				IMAGETYPE_GIF => imagegif($this->gdImage, $this->bufferStream),
				IMAGETYPE_WEBP => imagewebp($this->gdImage, $this->bufferStream),
				default => false,
			};
			if (!$success) {
				throw new MediaFileOperationException('Failed to write image');
			}

			rewind($this->bufferStream);

			// TODO: Re-enable image optimization again after migration to streams
			// Optimize image
			/* if (Configs::get_value('lossless_optimization', '0') == '1') {
				ImageOptimizer::optimize($destination);
			}*/

			return $this->bufferStream;
		} catch (\Throwable $e) {
			$this->close();
			throw new MediaFileOperationException('Failed to write image', $e);
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
			$this->gdImage = imagerotate($this->gdImage, $angle, 0);
			if (!$this->gdImage) {
				throw new ImageProcessingException('Failed to rotate image');
			}
		}

		if ($flip !== 0) {
			if (!imageflip($this->gdImage, $flip)) {
				throw new ImageProcessingException('Failed to flip image');
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function scale(ImageDimension $dstDim): ImageDimension
	{
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

		try {
			$image = imagecreatetruecolor($width, $height);
			if (!$image) {
				throw new ImageProcessingException('imagecreatetruecolor failed');
			}
			$this->fastImageCopyResampled($image, $this->gdImage, 0, 0, 0, 0, $width, $height, $srcDim->width, $srcDim->height);
			$this->gdImage = $image;
		} catch (\Throwable $e) {
			$this->reset();
			throw new ImageProcessingException('Failed to scale image', $e);
		}

		return new ImageDimension($width, $height);
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(ImageDimension $dstDim): void
	{
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

		try {
			$image = imagecreatetruecolor($dstDim->width, $dstDim->height);
			if (!$image) {
				throw new ImageProcessingException('imagecreatetruecolor failed');
			}
			$this->fastImageCopyResampled($image, $this->gdImage, 0, 0, $x, $y, $dstDim->width, $dstDim->height, $width, $height);
			$this->gdImage = $image;
		} catch (\Throwable $e) {
			$this->reset();
			throw new ImageProcessingException('Failed to scale image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(int $angle): ImageDimension
	{
		$this->gdImage = imagerotate($this->gdImage, -$angle, 0);
		if (!$this->gdImage) {
			throw new ImageProcessingException('Failed to rotate image');
		}

		return $this->getDimensions();
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
		\GdImage $dst_image,
		\GdImage $src_image,
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
		if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
			$temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
			if (!$temp) {
				throw new ImageProcessingException('imagecreatetruecolor failed');
			}
			if (!imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h)) {
				throw new ImageProcessingException('imagecopyresized failed');
			}
			if (!imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality)) {
				throw new ImageProcessingException('imagecopyresampled failed');
			}
		} else {
			if (!imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)) {
				throw new ImageProcessingException('imagecopyresampled failed');
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDimensions(): ImageDimension
	{
		$width = imagesx($this->gdImage);
		$height = imagesy($this->gdImage);

		if ($width === false || $height === false) {
			throw new ImageProcessingException('Could not determine dimensions of image');
		}

		return new ImageDimension($width, $height);
	}
}
