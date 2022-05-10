<?php

namespace App\Assets;

use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Image\FlysystemFile;
use App\Models\SizeVariant;

class SizeVariantLegacyNamingStrategy extends SizeVariantBaseNamingStrategy
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
	 * {@inheritDoc}
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
}
