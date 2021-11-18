<?php

namespace App\Image;

use App\Models\Configs;

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
	 * {@inheritDoc}
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array
	{
		$i = 0;
		$ret = [false, false];
		while ($i < count($this->engines) && ($ret = $this->engines[$i]->autoRotate($path, $orientation, $pretend)) == [false, false]) {
			$i++;
		}

		return $ret;
	}

	/**
	 * @param string $source
	 * @param int    $angle
	 * @param string $destination
	 *
	 * @return bool
	 */
	public function rotate(string $source, int $angle, string $destination = null): bool
	{
		if ($angle != 90 && $angle != -90) {
			return false;
		}

		$i = 0;
		while ($i < count($this->engines) && !$this->engines[$i]->rotate($source, $angle, $destination)) {
			$i++;
		}

		return $i != count($this->engines);
	}
}
