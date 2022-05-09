<?php

namespace App\Assets;

use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\MissingValueException;
use App\Image\FlysystemFile;
use App\Models\Photo;
use App\Models\SizeVariant;

class SizeVariantLegacyNamingStrategy extends SizeVariantNamingStrategy
{
	/**
	 * Maps a size variant to the path prefix (directory) where the file for that size variant is stored.
	 */
	public const VARIANT_2_PATH_PREFIX = [
		SizeVariant::THUMB => 'thumb',
		SizeVariant::THUMB2X => 'thumb',
		SizeVariant::SMALL => 'small',
		SizeVariant::SMALL2X => 'small',
		SizeVariant::MEDIUM => 'medium',
		SizeVariant::MEDIUM2X => 'medium',
		SizeVariant::ORIGINAL => 'big',
	];

	/**
	 * The file extension which is always used by both "thumb" variants and
	 * also by all other size variants but the original, if the original media
	 * file is not a photo.
	 * If the original media file is a photo, then the "small" and "medium"
	 * size variants use the same extension as the original file.
	 */
	public const DEFAULT_EXTENSION = '.jpeg';

	protected string $originalExtension = '';

	public function setPhoto(?Photo $photo): void
	{
		parent::setPhoto($photo);
		$this->originalExtension = '';
		if ($this->photo && $sv = $this->photo->size_variants->getOriginal()) {
			$this->originalExtension = $sv->getFile()->getOriginalExtension();
		}
	}

	/**
	 * Generates a short path for the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return FlysystemFile the file
	 *
	 * @throws InvalidSizeVariantException
	 * @throws IllegalOrderOfOperationException
	 * @throws MissingValueException
	 */
	public function createFile(int $sizeVariant): FlysystemFile
	{
		if (SizeVariant::ORIGINAL > $sizeVariant || $sizeVariant > SizeVariant::THUMB) {
			throw new InvalidSizeVariantException('invalid $sizeVariant = ' . $sizeVariant);
		}
		if ($this->photo == null) {
			throw new IllegalOrderOfOperationException('associated photo model must not be null');
		}
		if (empty($this->photo->original_checksum)) {
			throw new IllegalOrderOfOperationException('cannot generate filename before checksum of photo has been set');
		}
		$directory = self::VARIANT_2_PATH_PREFIX[$sizeVariant] . '/';
		if ($sizeVariant === SizeVariant::ORIGINAL && $this->photo->isRaw()) {
			$directory = 'raw/';
		}
		$filename = substr($this->photo->original_checksum, 0, 32);
		if ($sizeVariant === SizeVariant::MEDIUM2X ||
			$sizeVariant === SizeVariant::SMALL2X ||
			$sizeVariant === SizeVariant::THUMB2X) {
			$filename .= '@2x';
		}
		$extension = $this->generateExtension($sizeVariant);

		return new FlysystemFile(parent::getImageDisk(), $directory . $filename . $extension);
	}

	/**
	 * Returns the file extension incl. the preceding dot.
	 *
	 * @throws MissingValueException
	 * @throws IllegalOrderOfOperationException
	 */
	protected function generateExtension(int $sizeVariant): string
	{
		if ($sizeVariant === SizeVariant::THUMB ||
			$sizeVariant === SizeVariant::THUMB2X ||
			($sizeVariant !== SizeVariant::ORIGINAL && $this->photo->isVideo()) ||
			($sizeVariant !== SizeVariant::ORIGINAL && $this->photo->isRaw())
		) {
			return self::DEFAULT_EXTENSION;
		} elseif (!empty($this->originalExtension)) {
			return $this->originalExtension;
		} else {
			if (empty($this->fallbackExtension)) {
				throw new MissingValueException('fallbackExtension');
			}

			return $this->fallbackExtension;
		}
	}
}
