<?php

namespace App\Image;

use App\Configs;

class ImageHandler implements ImageHandlerInterface
{
	/**
	 * @var int
	 */
	private $compressionQuality;
	private $engines;

	/**
	 * @param int $compressionQuality
	 */
	public function __construct(int $compressionQuality)
	{
		$this->compressionQuality = $compressionQuality;
		$this->engines = [];
		if (Configs::hasImagick()) {
			$this->engines[] = new ImagickHandler($this->compressionQuality);
		}
		$this->engines[] = new GdHandler($this->compressionQuality);
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param int    $newWidth
	 * @param int    $newHeight
	 * @param int    &$resWidth
	 * @param int    &$resHeight
	 *
	 * @return bool
	 */
	public function scale(string $source, string $destination, int $newWidth, int $newHeight, int &$resWidth, int &$resHeight): bool
	{
		$i = 0;
		while ($i < count($this->engines) && !$this->engines[$i]->scale($source, $destination, $newWidth, $newHeight, $resWidth, $resHeight)) {
			$i++;
		}

		return $i != count($this->engines);
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param int    $newWidth
	 * @param int    $newHeight
	 *
	 * @return bool
	 */
	public function crop(string $source, string $destination, int $newWidth, int $newHeight): bool
	{
		$i = 0;
		while ($i < count($this->engines) && !$this->engines[$i]->crop($source, $destination, $newWidth, $newHeight)) {
			$i++;
		}

		return $i != count($this->engines);
	}

	/**
	 * Rotates and flips a photo based on its EXIF orientation.
	 *
	 * @param string $path
	 * @param array  $info
	 *
	 * @return array
	 */
	public function autoRotate(string $path, array $info): array
	{
		return $this->engines[0]->autoRotate($path, $info);
	}
}
