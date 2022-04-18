<?php

namespace App\Image;

use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Models\Configs;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class GdHandler implements ImageHandlerInterface
{
	public const SUPPORTED_IMAGE_TYPES = [
		IMAGETYPE_JPEG,
		IMAGETYPE_JPEG2000,
		IMAGETYPE_PNG,
		IMAGETYPE_GIF,
		IMAGETYPE_WEBP,
	];

	/** @var int the desired compression quality, only used for JPEG during save */
	private int $compressionQuality = 75;

	/** @var \GdImage|null the opaque GD handler */
	private ?\GdImage $gdImage = null;

	/** @var int the image type detected by GD upon loading */
	private int $gdImageType = 0;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(int $compressionQuality)
	{
		$this->compressionQuality = $compressionQuality;
		$this->reset();
	}

	private function reset(): void
	{
		if ($this->gdImage) {
			imagedestroy($this->gdImage);
		}
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
		// a "download" stream) is only readable once.
		$tmpStream = fopen('php://memory', 'r+');
		if (stream_copy_to_stream($stream, $tmpStream) === false) {
			throw new MediaFileOperationException('Could not read input stream');
		}

		// Determine the type of image, so that we can later save the
		// image using the same type
		try {
			list(, , $this->gdImageType) = getimagesize(stream_get_contents($tmpStream));
			rewind($tmpStream);
		} catch (\Throwable $e) {
			$this->reset();
			throw new MediaFileOperationException('Could not determine type of image', $e);
		}
		if (!in_array($this->gdImageType, self::SUPPORTED_IMAGE_TYPES)) {
			$this->reset();
			fclose($tmpStream);
			throw new MediaFileUnsupportedException('Type of photo is not supported');
		}

		// Load image
		try {
			$this->gdImage = imagecreatefromstring(stream_get_contents($stream));
			if ($this->gdImage === false) {
				throw new MediaFileOperationException('Could not read input stream');
			}
			rewind($tmpStream);
		} catch (\Throwable $e) {
			$this->reset();
			fclose($tmpStream);
			throw new MediaFileOperationException('Could not load image', $e);
		}

		// Get EXIF data to determine whether rotation is required
		try {
			// TODO: We should use PHPexif here, after is also has support for streams
			$exifData = exif_read_data($tmpStream);
			if ($exifData === false) {
				throw new MediaFileOperationException('Could not read EXIF data');
			}
		} catch (\Throwable $e) {
			$this->reset();
			fclose($tmpStream);
			throw new MediaFileOperationException('Could not load image', $e);
		}
		fclose($tmpStream);

		// Auto-rotate image
		$orientation = !empty($exifData['Orientation']) ? $exifData['Orientation'] : 1;
		$this->autoRotateInternal($orientation);
	}

	/**
	 * {@inheritDoc}
	 */
	public function save()
	{
		if (!$this->gdImage) {
			new MediaFileOperationException('No image loaded');
		}
		try {
			$tmpStream = fopen('php://memory', 'r+');
			$success = match ($this->gdImageType) {
				IMAGETYPE_JPEG, IMAGETYPE_JPEG2000 => imagejpeg($this->gdImage, $tmpStream, $this->compressionQuality),
				IMAGETYPE_PNG => imagepng($this->gdImage, $tmpStream),
				IMAGETYPE_GIF => imagegif($this->gdImage, $tmpStream),
				IMAGETYPE_WEBP => imagewebp($this->gdImage, $tmpStream),
				default => false,
			};
			if (!$success) {
				throw new MediaFileOperationException('Failed to write image');
			}

			$this->reset();
			rewind($tmpStream);

			// TODO: Re-enable image optimization again after migration to streams
			// Optimize image
			/**if (Configs::get_value('lossless_optimization', '0') == '1') {
				ImageOptimizer::optimize($destination);
			}*//

			return $tmpStream;
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Could not save image', $e);
		}
	}

	/**
	 * Rotates the image based on the given orientation.
	 *
	 * @param int $orientation the orientation of the original image
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	private function autoRotateInternal(int $orientation): void
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
			$tmpImage = imagerotate($this->gdImage, $angle, 0);
			if ($tmpImage === false) {
				$this->reset();
				throw new MediaFileOperationException('Failed to rotate image');
			}
			imagedestroy($this->gdImage);
			$this->gdImage = $tmpImage;
		}

		if ($flip !== 0) {
			if (imageflip($this->gdImage, $flip) === false) {
				$this->reset();
				throw new MediaFileOperationException('Failed to flip image');
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function scale(
		int $newWidth,
		int $newHeight,
		int &$resWidth,
		int &$resHeight
	): void {
		try {
			$width = imagesx($this->gdImage);
			$height = imagesy($this->gdImage);

			if ($width === false || $height === false) {
				throw new MediaFileOperationException('Could not determine dimensions of image');
			}

			if ($newWidth === 0 && $newHeight == 0) {
				throw new LycheeDomainException('Width and height must not be zero simultaneously');
			}

			if ($newWidth === 0) {
				$newWidth = (int)round($newHeight * ($width / $height));
			} else {
				$tmpHeight = (int)round($newWidth / ($width / $height));
				if ($newHeight !== 0 && $tmpHeight > $newHeight) {
					$newWidth = (int)round($newHeight * ($width / $height));
				} else {
					$newHeight = $tmpHeight;
				}
			}

			$image = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($image, $this->gdImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagedestroy($this->gdImage);
			$this->gdImage = $image;

			$resWidth = $newWidth;
			$resHeight = $newHeight;
		} catch (\Throwable $e) {
			$this->reset();
			throw new MediaFileOperationException('Failed to scale image', $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight
	): void {
		$res = $this->readImage($source);
		list($sourceImg, , $width, $height) = $res;

		if ($width < $height) {
			$newSize = $width;
			$startWidth = 0;
			$startHeight = $height / 2 - $width / 2;
		} else {
			$newSize = $height;
			$startWidth = $width / 2 - $height / 2;
			$startHeight = 0;
		}

		$image = imagecreatetruecolor($newWidth, $newHeight);

		$this->fastImageCopyResampled($image, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth, $newHeight, $newSize, $newSize);

		if (imagejpeg($image, $destination, $this->compressionQuality) === false) {
			throw new MediaFileOperationException('Failed to write image ' . $destination);
		}

		imagedestroy($image);
		imagedestroy($sourceImg);
	}

	/**
	 * {@inheritdoc}
	 */
	public function autoRotate(int $orientation = 1): array
	{
		$image = imagecreatefromjpeg($path);
		if ($image === false) {
			throw new MediaFileOperationException('Failed to read image ' . $path);
		}

		$rotate = $orientation !== 1;

		$dimensions = $this->autoRotateInternal($image, $orientation);

		if ($rotate && !$pretend) {
			if (!imagejpeg($image, $path, 100)) {
				throw new MediaFileOperationException('Failed to write image ' . $path);
			}
		}

		imagedestroy($image);

		return $dimensions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(int $angle): void
	{
		$res = $this->readImage($source);
		list($image, $mime) = $res;

		$image = imagerotate($image, -$angle, 0);
		if ($image === false) {
			throw new MediaFileOperationException('Failed to rotate image ' . $source);
		}

		$this->writeImage($destination ?? $source, $image, $mime, 100);

		imagedestroy($image);
	}

	/**
	 * Plug-and-Play fastImageCopyResampled function replaces much slower imagecopyresampled.
	 * Just include this function and change all "imagecopyresampled" references to "fastImageCopyResampled".
	 * Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
	 * Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
	 *
	 * Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
	 * Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
	 * 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
	 * 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
	 * 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
	 * 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
	 * 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
	 *
	 * @param resource &$dst_image
	 * @param resource $src_image
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
	 */
	private function fastImageCopyResampled(
		&$dst_image,
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
		if (empty($src_image) || empty($dst_image) || $quality <= 0) {
			return;
		}

		if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
			$temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
			imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
			imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
			imagedestroy($temp);
		} else {
			imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		}
	}
}
