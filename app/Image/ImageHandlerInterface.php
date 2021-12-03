<?php

namespace App\Image;

interface ImageHandlerInterface
{
	/**
	 * @param int $compressionQuality
	 */
	public function __construct(int $compressionQuality);

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
	public function scale(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight,
		int &$resWidth,
		int &$resHeight
	): bool;

	/**
	 * @param string $source
	 * @param string $destination
	 * @param int    $newWidth
	 * @param int    $newHeight
	 *
	 * @return bool
	 */
	public function crop(
		string $source,
		string $destination,
		int $newWidth,
		int $newHeight
	): bool;

	/**
	 * Rotates and flips a photo based on its EXIF orientation.
	 *
	 * @param string $path
	 * @param int    $orientation the orientation value (1..8) as defined by EXIF specification, default is 1 (means up-right and not mirrored/flipped)
	 * @param bool   $pretend
	 *
	 * @return array an associative array `['width' => (int), 'height' => (int)]` with the new width and height after rotation
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array;

	/**
	 * @param string $source
	 * @param int    $angle
	 * @param string $destination
	 *
	 * @return bool
	 */
	public function rotate(string $source, int $angle, string $destination = null): bool;
}
