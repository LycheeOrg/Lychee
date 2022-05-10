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

	/**
	 * Returns the disk on which the size variants are put.
	 *
	 * @return Filesystem
	 */
	public static function getImageDisk(): Filesystem
	{
		return Storage::disk(self::IMAGE_DISK_NAME);
	}

	/**
	 * Sets a fallback extension for the size variants.
	 *
	 * This extension is used, if the correct extension cannot be inferred
	 * from the photo.
	 *
	 * @param string $fallbackExtension the fallback extension, incl. a preceding dot.
	 *
	 * @return void
	 */
	public function setFallbackExtension(string $fallbackExtension): void
	{
		$this->fallbackExtension = $fallbackExtension;
	}

	/**
	 * Sets the photo for which names of size variants shall be generated.
	 *
	 * @param Photo|null $photo the photo whose size variants shall be named
	 *
	 * @return void
	 */
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
	 *
	 * @throws LycheeException
	 */
	abstract public function createFile(int $sizeVariant): FlysystemFile;
}
