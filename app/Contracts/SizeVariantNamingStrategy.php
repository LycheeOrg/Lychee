<?php

namespace App\Contracts;

use App\Image\FlysystemFile;
use App\Models\Photo;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Interface SizeVariantNamingStrategy.
 */
abstract class SizeVariantNamingStrategy
{
	/**
	 * The name of the Flysystem disk where images are stored.
	 */
	public const IMAGE_DISK_NAME = 'images';

	protected string $fallbackExtension = '';
	protected ?Photo $photo = null;

	public static function getImageDisk(): Filesystem
	{
		return Storage::disk(self::IMAGE_DISK_NAME);
	}

	public function setFallbackExtension(string $fallbackExtension): void
	{
		$this->fallbackExtension = $fallbackExtension;
	}

	public function setPhoto(?Photo $photo): void
	{
		$this->photo = $photo;
	}

	/**
	 * Creates a file for the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return FlysystemFile the file
	 */
	abstract public function createFile(int $sizeVariant): FlysystemFile;
}
