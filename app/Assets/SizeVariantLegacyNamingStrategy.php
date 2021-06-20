<?php

namespace App\Assets;

use App\Contracts\SizeVariantNamingStrategy;
use App\Models\SizeVariant;

class SizeVariantLegacyNamingStrategy extends SizeVariantNamingStrategy
{
	/**
	 * Maps a size variant to the path prefix (directory) where the file for that size variant is stored.
	 */
	const VARIANT_2_PATH_PREFIX = [
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
	const DEFAULT_EXTENSION = '.jpeg';

	/**
	 * Generates a short path for the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return string The short path
	 */
	public function generateShortPath(int $sizeVariant): string
	{
		if (SizeVariant::ORIGINAL > $sizeVariant || $sizeVariant > SizeVariant::THUMB) {
			throw new \InvalidArgumentException('invalid $sizeVariant = ' . $sizeVariant);
		}
		if ($this->sourceFileInfo == null || empty($this->sourceFileInfo->getOriginalFileExtension())) {
			throw new \InvalidArgumentException('file extension of original file must not be empty');
		}
		if ($this->photo == null) {
			throw new \InvalidArgumentException('file extension of original file must not be empty');
		}
		if (empty($this->photo->checksum)) {
			throw new \BadFunctionCallException('cannot generate short path for photo before checksum has been set');
		}
		$directory = self::VARIANT_2_PATH_PREFIX[$sizeVariant] . '/';
		if ($sizeVariant === SizeVariant::ORIGINAL && $this->photo->isRaw()) {
			$directory = 'raw/';
		}
		$filename = substr($this->photo->checksum, 0, 32);
		if ($sizeVariant === SizeVariant::MEDIUM2X ||
			$sizeVariant === SizeVariant::SMALL2X ||
			$sizeVariant === SizeVariant::THUMB2X) {
			$filename .= '@2x';
		}
		$extension = $this->sourceFileInfo->getOriginalFileExtension();
		if ($sizeVariant === SizeVariant::THUMB ||
			$sizeVariant == SizeVariant::THUMB2X ||
			($sizeVariant !== SizeVariant::ORIGINAL && $this->photo->isVideo()) ||
			($sizeVariant !== SizeVariant::ORIGINAL && $this->photo->isRaw())
		) {
			$extension = self::DEFAULT_EXTENSION;
		}

		return $directory . $filename . $extension;
	}

	/**
	 * Given a filename generate the @2x corresponding filename.
	 * This is used for thumbs, small and medium.
	 */
	protected function ex2x(string $filename): string
	{
		$filename2x = explode('.', $filename);

		return (count($filename2x) === 2) ?
			$filename2x[0] . '@2x.' . $filename2x[1] :
			$filename2x[0] . '@2x';
	}
}