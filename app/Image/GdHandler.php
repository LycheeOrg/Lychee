<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Image;

use App\Logs;

class GdHandler implements ImageHandlerInterface
{
	/**
	 * @var int
	 */
	private $compressionQuality;

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
		list($width, $height, $mime) = getimagesize($source);

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
		$sourceImg = $this->createImage($source, $mime);

		if ($sourceImg === false) {
			return false;
		}

		imagecopyresampled($image, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		switch ($mime) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				imagejpeg($image, $destination, $this->compressionQuality);
				break;
			case IMAGETYPE_PNG:
				imagepng($image, $destination);
				break;
			case IMAGETYPE_GIF:
				imagegif($image, $destination);
				break;
			// createImage above already checked for any invalid values
		}

		imagedestroy($image);
		imagedestroy($sourceImg);

		$resWidth = $newWidth;
		$resHeight = $newHeight;

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
		list($width, $height, $mime) = getimagesize($source);

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
		$sourceImg = $this->createImage($source, $mime);

		if ($sourceImg === false) {
			return false;
		}

		$this->fastImageCopyResampled($image, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth, $newHeight, $newSize, $newSize);
		imagejpeg($image, $destination, $this->compressionQuality);

		imagedestroy($image);
		imagedestroy($sourceImg);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function autoRotate(string $path, array $info): array
	{
		$image = imagecreatefromjpeg($path);

		$rotate = true;
		switch ($info['orientation']) {
			case 1:
				$rotate = false;
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
		}

		if ($rotate) { // we only rotate if there is a need. Fixes #111
			imagejpeg($image, $path, 100);
		}

		imagedestroy($image);

		list($width, $height) = getimagesize($path);

		return ['width' => $width, 'height' => $height];
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
	 * @param int    $mime
	 *
	 * @return resource|bool|null
	 */
	private function createImage(string $source, int $mime)
	{
		switch ($mime) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				return imagecreatefromjpeg($source);
				break;
			case IMAGETYPE_PNG:
				return imagecreatefrompng($source);
				break;
			case IMAGETYPE_GIF:
				return imagecreatefromgif($source);
				break;
			default:
				Logs::error(__METHOD__, __LINE__, 'Type of photo "' . $mime . '" is not supported');

				return false;
				break;
		}
	}
}
