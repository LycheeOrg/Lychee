<?php

namespace App\Contracts\Models;

use App\Contracts\Exceptions\LycheeException;
use App\Enum\SizeVariantType;
use App\Image\Files\FlysystemFile;
use App\Models\Photo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * Interface SizeVariantNamingStrategy.
 */
abstract class AbstractSizeVariantNamingStrategy
{
	/**
	 * The name of the Flysystem disk where images are stored.
	 */
	public const IMAGE_DISK_NAME = 'images';
	public const S3_IMAGE_DISK_NAME = 's3';

	protected string $extension = '';
	protected ?Photo $photo = null;

	/**
	 * Returns the disk on which the size variants are put.
	 *
	 * @return FilesystemAdapter
	 */
	public static function getImageDisk(): FilesystemAdapter
	{
		return config('filesystems.disks.s3.key') !== ''
			? Storage::disk(self::S3_IMAGE_DISK_NAME)
			: Storage::disk(self::IMAGE_DISK_NAME);
	}

	/**
	 * Sets the extension to be used for the size variants.
	 *
	 * {@link SizeVariantNamingStrategy::setPhoto()} also sets the
	 * extension, if the photo is linked to an original size variant.
	 * Hence, calling this method should only be necessary for creating new
	 * photos, if no size variant already exist.
	 *
	 * @param string $extension the extension
	 *
	 * @return void
	 */
	public function setExtension(string $extension): void
	{
		$this->extension = $extension;
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
		$this->extension = '';
		if ($this->photo !== null && ($sv = $this->photo->size_variants->getOriginal()) !== null) {
			$this->extension = $sv->getFile()->getExtension();
		}
	}

	/**
	 * Creates a file for the designated size variant.
	 *
	 * @param SizeVariantType $sizeVariant the size variant
	 *
	 * @return FlysystemFile the file
	 *
	 * @throws LycheeException
	 */
	abstract public function createFile(SizeVariantType $sizeVariant): FlysystemFile;
}
