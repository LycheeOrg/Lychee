<?php

namespace App\Assets;

use App\Contracts\SizeVariantNamingStrategy;
use App\Facades\Helpers;
use App\Models\Photo;
use App\Models\SizeVariant;

class SizeVariantLegacyNamingStrategy extends SizeVariantNamingStrategy
{
	protected string $originalExtension = '';

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

	public function setPhoto(?Photo $photo)
	{
		parent::setPhoto($photo);
		$this->originalExtension = '';
		if ($this->photo) {
			$sv = $this->photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
			if ($sv) {
				if (!empty($sv->short_path)) {
					$this->originalExtension = Helpers::getExtension($sv->short_path, false);
				}
			}
		}
	}

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
		if ($this->photo == null) {
			throw new \InvalidArgumentException('associated photo model must not be null');
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
		$extension = $this->generateExtension($sizeVariant);

		return $directory . $filename . $extension;
	}

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
				throw new \LogicException('file extension must not be empty');
			}

			return $this->fallbackExtension;
		}
	}
}
