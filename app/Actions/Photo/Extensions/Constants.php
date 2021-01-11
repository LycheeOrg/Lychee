<?php

namespace App\Actions\Photo\Extensions;

trait Constants
{
	/**
	 * @var array
	 */
	public $validTypes = [
		IMAGETYPE_JPEG,
		IMAGETYPE_GIF,
		IMAGETYPE_PNG,
		IMAGETYPE_WEBP,
	];

	/**
	 * @var array
	 */
	public $validVideoTypes = [
		'video/mp4',
		'video/mpeg',
		'image/x-tga', // mpg; will be corrected by the metadata extractor
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-ms-asf', // wmv file
		'video/x-ms-wmv', // wmv file
		'video/x-msvideo', // Avi
		'video/x-m4v', // Avi
		'application/octet-stream', // Some mp4 files; will be corrected by the metadata extractor
	];

	/**
	 * @var array
	 */
	public $validExtensions = [
		'.jpg',
		'.jpeg',
		'.png',
		'.gif',
		'.ogv',
		'.mp4',
		'.mpg',
		'.webm',
		'.webp',
		'.mov',
		'.m4v',
		'.avi',
		'.wmv',
	];

	/**
	 * Validates whether $type is a valid image type.
	 *
	 * @param int $type
	 *
	 * @return bool
	 */
	public function isValidImageType(int $type): bool
	{
		return in_array($type, $this->validTypes, true);
	}

	/**
	 * Returns a list of valid image types.
	 *
	 * @return array
	 */
	public function getValidImageTypes(): array
	{
		return $this->validTypes;
	}

	/**
	 * Validates whether $type is a valid video type.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function isValidVideoType(string $type): bool
	{
		return in_array($type, $this->validVideoTypes, true);
	}

	/**
	 * Returns a list of valid video types.
	 *
	 * @return array
	 */
	public function getValidVideoTypes(): array
	{
		return $this->validVideoTypes;
	}

	/**
	 * Validates whether $extension is a valid image or video extension.
	 *
	 * @param string $extension
	 *
	 * @return bool
	 */
	public function isValidExtension(string $extension): bool
	{
		return in_array(strtolower($extension), $this->validExtensions, true);
	}

	/**
	 * Returns a list of valid image/video extensions.
	 *
	 * @return array
	 */
	public function getValidExtensions(): array
	{
		return $this->validExtensions;
	}
}
