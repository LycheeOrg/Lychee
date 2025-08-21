<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Assets;

use App\Enum\SizeVariantType;
use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;

/**
 * We extend the SizeVariant naming strategy for watermarks.
 * This allows us to take new random middle paths for the file.
 */
class WatermarkGroupedWithRandomSuffixNamingStrategy extends SizeVariantGroupedWithRandomSuffixNamingStrategy
{
	/**
	 * {@inheritDoc}
	 *
	 * All watermarks are jpeg. We do not bother with other extensions.
	 */
	final protected function generateExtension(SizeVariantType $size_variant): string
	{
		return self::THUMB_EXTENSION;
	}

	/**
	 * Get the middle path from the size variant.
	 *
	 * @param SizeVariant $size_variant
	 *
	 * @return void
	 */
	public function setFromSizeVariant(SizeVariant $size_variant): void
	{
		$this->extension = self::THUMB_EXTENSION;
		$this->photo = null;

		// We do not support setting a photo, as this is a watermark strategy.
		// Instead, we set the random middle path from the size variant.
		if (Configs::getValueAsBool('watermark_random_path')) {
			$this->cachedRndMiddlePath = self::createRndMiddlePath();

			return;
		}

		$this->cachedRndMiddlePath = $this->getRndMiddleFromPath(
			$size_variant->getFile()->getRelativePath(),
			$size_variant->type
		) ?? self::createRndMiddlePath();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws InsufficientEntropyException
	 */
	final public function setPhoto(?Photo $photo): void
	{
		throw new LycheeLogicException('WatermarkGroupedWithRandomSuffixNamingStrategy does not support setting a photo. Use the setRndMiddlePath instead.');
	}
}
