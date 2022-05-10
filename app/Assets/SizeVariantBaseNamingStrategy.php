<?php

namespace App\Assets;

use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\MissingValueException;
use App\Models\Photo;
use App\Models\SizeVariant;

abstract class SizeVariantBaseNamingStrategy extends SizeVariantNamingStrategy
{
	/**
	 * The file extension which is always used by both "thumb" variants and
	 * also by all other size variants but the original, if the original media
	 * file is not a photo.
	 * If the original media file is a photo, then the "small" and "medium"
	 * size variants use the same extension as the original file.
	 */
	public const DEFAULT_EXTENSION = '.jpeg';

	protected string $originalExtension = '';

	/**
	 * {@inheritDoc}
	 */
	public function setPhoto(?Photo $photo): void
	{
		parent::setPhoto($photo);
		$this->originalExtension = '';
		if ($this->photo && $sv = $this->photo->size_variants->getOriginal()) {
			$this->originalExtension = $sv->getFile()->getOriginalExtension();
		}
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
			($sizeVariant !== SizeVariant::ORIGINAL && !$this->photo->isPhoto())
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