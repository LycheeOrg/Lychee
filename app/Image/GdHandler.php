<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Image;

use App\Models\Configs;
use App\Models\Logs;
use ImageOptimizer;

class GdHandler implements ImageHandlerInterface
{
	/**
	 * @var int
	 */
	private $compressionQuality;

	/**
	 * Rotates a given image resource based on the given orientation.
	 *
	 * @param resource $image       the image reference to rotate
	 * @param int      $orientation the orientation of the original image
	 *
	 * @return array a dictionary of width and height of the rotated image
	 */
	private function autoRotateInternal(&$image, int $orientation): array
	{
		switch ($orientation) {
			case 1:
				// nothing to do
				break;
			case 2:
				imageflip($image, IMG_FLIP_HORIZONTAL);
				break;

			case 3:
				$image = imagerotate($image, -180, 0);
				break;

			case 4:
				imageflip($image, IMG_FLIP_VERTICAL);
				break;

			case 5:
				$image = imagerotate($image, -90, 0);
				imageflip($image, IMG_FLIP_HORIZONTAL);
				break;

			case 6:
				$image = imagerotate($image, -90, 0);
				break;

			case 7:
				$image = imagerotate($image, 90, 0);
				imageflip($image, IMG_FLIP_HORIZONTAL);
				break;

			case 8:
				$image = imagerotate($image, 90, 0);
				break;

			default:
				break;
		}

		return ['width' => imagesx($image), 'height' => imagesy($image)];
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
	): bool {
		$res = $this->readImage($source);
		if ($res === false) {
			return false;
		}
		list($sourceImg, $mime, $width, $height) = $res;

		if ($newWidth == 0) {
			$newWidth = $newHeight * ($width / $height);
		} else {
			$tmpHeight = $newWidth / ($width / $height);
			if ($newHeight != 0 && $tmpHeight > $newHeight) {
				$newWidth = $newHeight * ($width / $height);
			} else {
				$newHeight = $tmpHeight;
			}
		}

		$image = imagecreatetruecolor($newWidth, $newHeight);

		imagecopyresampled($image, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		if ($this->writeImage($destination, $image, $mime) === false) {
			return false;
		}

		imagedestroy($image);
		imagedestroy($sourceImg);

		$resWidth = $newWidth;
		$resHeight = $newHeight;

		// Optimize image
		if (Configs::get_value('lossless_optimization', '0') == '1') {
			ImageOptimizer::optimize($destination);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function crop(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight
	): bool {
		$res = $this->readImage($source);
		if ($res === false) {
			return false;
		}
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
			return false;
		}

		imagedestroy($image);
		imagedestroy($sourceImg);

		// Optimize image
		if (Configs::get_value('lossless_optimization', '0') == '1') {
			ImageOptimizer::optimize($destination);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array
	{
		$image = imagecreatefromjpeg($path);

		$rotate = $orientation !== 1;

		$dimensions = $this->autoRotateInternal($image, $orientation);

		if ($rotate && !$pretend) {
			imagejpeg($image, $path, 100);
		}

		imagedestroy($image);

		return $dimensions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rotate(string $source, int $angle, string $destination = null): bool
	{
		$res = $this->readImage($source);
		if ($res === false) {
			return false;
		}
		list($image, $mime) = $res;

		$image = imagerotate($image, -$angle, 0);
		if ($image === false) {
			return false;
		}

		$ret = $this->writeImage($destination ?? $source, $image, $mime, 100);

		imagedestroy($image);

		return $ret;
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
	 * @return bool
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
	): bool {
		if (empty($src_image) || empty($dst_image) || $quality <= 0) {
			return false;
		}

		if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
			$temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
			imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
			imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
			imagedestroy($temp);
		} else {
			imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		}

		return true;
	}

	/**
	 * @param string $source
	 *
	 * @return array|false
	 */
	private function readImage(string $source)
	{
		list(, , $mime) = getimagesize($source);

		switch ($mime) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				$image = imagecreatefromjpeg($source);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($source);
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($source);
				break;
			case IMAGETYPE_WEBP:
				$image = imagecreatefromwebp($source);
				break;
			default:
				Logs::error(__METHOD__, __LINE__, 'Type of photo "' . $mime . '" is not supported');

				return false;
				break;
		}

		if ($image === false) {
			return false;
		}

		// the image may need to be rotated prior to any processing
		try {
			$exif = exif_read_data($source);
		} catch (\Exception $e) {
			$exif = [];
		}
		$orientation = isset($exif['Orientation']) && $exif['Orientation'] !== '' ? $exif['Orientation'] : 1;
		$dimensions = $this->autoRotateInternal($image, $orientation);

		return [$image, $mime, $dimensions['width'], $dimensions['height']];
	}

	/**
	 * @param string   $destination
	 * @param resource $image
	 * @param int      $mime
	 * @param int      $quality
	 *
	 * @return bool
	 */
	private function writeImage(string $destination, $image, int $mime, int $quality = null): bool
	{
		$ret = false;

		switch ($mime) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				$ret = imagejpeg($image, $destination, $quality ?? $this->compressionQuality);
				break;
			case IMAGETYPE_PNG:
				$ret = imagepng($image, $destination);
				break;
			case IMAGETYPE_GIF:
				$ret = imagegif($image, $destination);
				break;
			case IMAGETYPE_WEBP:
				$ret = imagewebp($image, $destination);
				break;
		}

		return $ret;
	}
}
