<?php

namespace App\Assets;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\MissingValueException;
use App\Models\SizeVariant;

abstract class BaseSizeVariantNamingStrategy extends AbstractSizeVariantNamingStrategy
{
	/**
	 * The file extension which is always used by both "thumb" variants.
	 * If the media file is not a supported photo format (e.g. the media is
	 * a video), then this extension is also used for the small and medium
	 * size variants.
	 */
	public const THUMB_EXTENSION = '.jpeg';

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
			return self::THUMB_EXTENSION;
		}

		if ($this->extension === '') {
			throw new MissingValueException('extension');
		}

		return $this->extension;
	}
}
