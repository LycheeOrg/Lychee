<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Assets;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\MissingValueException;

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
	 * The file extension which is always used by placeholder variants.
	 */
	public const PLACEHOLDER_EXTENSION = '.webp';

	/**
	 * Returns the file extension incl. the preceding dot.
	 *
	 * @throws MissingValueException
	 * @throws IllegalOrderOfOperationException
	 */
	protected function generateExtension(SizeVariantType $sizeVariant): string
	{
		if ($sizeVariant === SizeVariantType::THUMB ||
			$sizeVariant === SizeVariantType::THUMB2X ||
			($sizeVariant !== SizeVariantType::ORIGINAL && !$this->photo->isPhoto())
		) {
			return self::THUMB_EXTENSION;
		}

		if ($sizeVariant === SizeVariantType::PLACEHOLDER) {
			return self::PLACEHOLDER_EXTENSION;
		}

		if ($this->extension === '') {
			// @codeCoverageIgnoreStart
			throw new MissingValueException('extension');
			// @codeCoverageIgnoreEnd
		}

		return $this->extension;
	}
}
