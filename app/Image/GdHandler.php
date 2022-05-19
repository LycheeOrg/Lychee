<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Models\Configs;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

/**
 * Note that the doc blocks of the GD image processing functions are incorrect.
 * PhpStan is reporting a lot of false posiive.
 * See here: https://github.com/thecodingmachine/safe/issues/283.
 */
class GdHandler implements ImageHandlerInterface
{
	private int $compressionQuality;

	/**
	 * Rotates a given image resource based on the given orientation.
	 *
	 * @param \GdImage $image       the image reference to rotate
	 * @param int      $orientation the orientation of the original image
	 *
	 * @return array{width: int, height: int} a dictionary of width and height of the rotated image
	 *
	 * @throws MediaFileOperationException
	 */
	private function autoRotateInternal(\GdImage &$image, int $orientation): array
	{
		try {
			$image = match ($orientation) {
				3 => \Safe\imagerotate($image, -180, 0),
				5 => \Safe\imagerotate($image, -90, 0),
				6 => \Safe\imagerotate($image, -90, 0),
				7 => \Safe\imagerotate($image, 90, 0),
				8 => \Safe\imagerotate($image, 90, 0),
				default => $image
			};
			match ($orientation) {
				2 => \Safe\imageflip($image, IMG_FLIP_HORIZONTAL),
				4 => \Safe\imageflip($image, IMG_FLIP_VERTICAL),
				5 => \Safe\imageflip($image, IMG_FLIP_HORIZONTAL),
				7 => \Safe\imageflip($image, IMG_FLIP_HORIZONTAL)
			};
		} catch (\Throwable) {
			throw new MediaFileOperationException('Failed to rotate image');
		}

		try {
			$width = \Safe\imagesx($image);
			$height = \Safe\imagesy($image);
		} catch (\Throwable) {
			throw new MediaFileOperationException('Failed to determine dimensions of image');
		}

		return ['width' => $width, 'height' => $height];
	}

	/**
	 * {@inheritdoc}
	 */
	public function __construct(int $compressionQuality)
	{
		$this->compressionQuality = $compressionQuality;
	}

	/**
	 * {@inheritdoc}
	 */
	public function scale(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight,
		int &$resWidth,
		int &$resHeight
	): void {
		$res = $this->readImage($source);
		$sourceImg = $res['image'];
		$mime = $res['mime'];
		$width = $res['width'];
		$height = $res['height'];

		if ($newWidth == 0) {
			$newWidth = (int) round($newHeight * ($width / $height));
		} else {
			$tmpHeight = (int) round($newWidth / ($width / $height));
			if ($newHeight != 0 && $tmpHeight > $newHeight) {
				$newWidth = (int) round($newHeight * ($width / $height));
			} else {
				$newHeight = $tmpHeight;
			}
		}

		$image = \Safe\imagecreatetruecolor($newWidth, $newHeight);

		\Safe\imagecopyresampled($image, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		$this->writeImage($destination, $image, $mime);

		\Safe\imagedestroy($image);
		\Safe\imagedestroy($sourceImg);

		$resWidth = $newWidth;
		$resHeight = $newHeight;

		// Optimize image
		if (Configs::get_value('lossless_optimization', '0') == '1') {
			ImageOptimizer::optimize($destination);
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
		$sourceImg = $res['image'];
		$width = $res['width'];
		$height = $res['height'];

		if ($width < $height) {
			$newSize = $width;
			$startWidth = 0;
			$startHeight = $height / 2 - $width / 2;
		} else {
			$newSize = $height;
			$startWidth = $width / 2 - $height / 2;
			$startHeight = 0;
		}

		$image = \Safe\imagecreatetruecolor($newWidth, $newHeight);

		$this->fastImageCopyResampled($image, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth, $newHeight, $newSize, $newSize);

		try {
			\Safe\imagejpeg($image, $destination, $this->compressionQuality);
		} catch (\Throwable) {
			throw new MediaFileOperationException('Failed to write image ' . $destination);
		}

		\Safe\imagedestroy($image);
		\Safe\imagedestroy($sourceImg);

		// Optimize image
		if (Configs::get_value('lossless_optimization', '0') == '1') {
			ImageOptimizer::optimize($destination);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array
	{
		try {
			/** @var \GdImage $image */
			$image = \Safe\imagecreatefromjpeg($path);
		} catch (\Throwable) {
			throw new MediaFileOperationException('Failed to read image ' . $path);
		}

		$rotate = $orientation !== 1;

		$dimensions = $this->autoRotateInternal($image, $orientation);

		if ($rotate && !$pretend) {
			try {
				\Safe\imagejpeg($image, $path, 100);
			} catch (\Throwable) {
				throw new MediaFileOperationException('Failed to write image ' . $path);
			}
		}
		\Safe\imagedestroy($image);

		return $dimensions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(string $source, int $angle, ?string $destination = null): void
	{
		$res = $this->readImage($source);
		$image = $res['image'];
		$mime = $res['mime'];

		try {
			$image = \Safe\imagerotate($image, -$angle, 0);
		} catch (\Throwable) {
			throw new MediaFileOperationException('Failed to rotate image ' . $source);
		}

		$this->writeImage($destination ?? $source, $image, $mime, 100);

		\Safe\imagedestroy($image);
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
	 * @param ?\GdImage &$dst_image
	 * @param ?\GdImage $src_image
	 * @param int       $dst_x
	 * @param int       $dst_y
	 * @param int       $src_x
	 * @param int       $src_y
	 * @param int       $dst_w
	 * @param int       $dst_h
	 * @param int       $src_w
	 * @param int       $src_h
	 * @param int       $quality
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
			$temp = \Safe\imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
			\Safe\imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
			\Safe\imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
			\Safe\imagedestroy($temp);
		} else {
			\Safe\imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		}
	}

	/**
	 * @param string $source
	 *
	 * @return array{image: \GdImage, mime: int, width: int, height: int}
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	private function readImage(string $source): array
	{
		$mime = \Safe\getimagesize($source)[2];

		try {
			/** @var \GdImage */
			$image = match ($mime) {
				IMAGETYPE_JPEG, IMAGETYPE_JPEG2000 => \Safe\imagecreatefromjpeg($source),
				IMAGETYPE_PNG => \Safe\imagecreatefrompng($source),
				IMAGETYPE_GIF => \Safe\imagecreatefromgif($source),
				IMAGETYPE_WEBP => \Safe\imagecreatefromwebp($source),
				default => throw new MediaFileUnsupportedException('Type of photo "' . $mime . '" is not supported'),
			};
		} catch (\Throwable) {
			throw new MediaFileUnsupportedException('Failed to read photo ' . $source);
		}

		// the image may need to be rotated prior to any processing
		try {
			$exif = exif_read_data($source) ?: [];
		} catch (\Exception $e) {
			$exif = [];
		}
		$orientation = isset($exif['Orientation']) && $exif['Orientation'] !== '' ? $exif['Orientation'] : 1;
		$dimensions = $this->autoRotateInternal($image, $orientation);

		return ['image' => $image, 'mime' => $mime, 'width' => $dimensions['width'], 'height' => $dimensions['height']];
	}

	/**
	 * @param string   $destination
	 * @param resource $image
	 * @param int      $mime
	 * @param int      $quality
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	private function writeImage(string $destination, $image, int $mime, int $quality = -1): void
	{
		try {
			match ($mime) {
				IMAGETYPE_JPEG, IMAGETYPE_JPEG2000 => \Safe\imagejpeg($image, $destination, $quality !== -1 ? $quality : $this->compressionQuality),
				IMAGETYPE_PNG => \Safe\imagepng($image, $destination),
				IMAGETYPE_GIF => \Safe\imagegif($image, $destination),
				IMAGETYPE_WEBP => \Safe\imagewebp($image, $destination),
				default => throw new MediaFileOperationException('Failed to write image ' . $destination),
			};
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Failed to write image ' . $destination, $e);
		}
	}
}
