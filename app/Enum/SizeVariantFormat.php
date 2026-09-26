<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * File format of the generated size variants (thumb, small, medium and their 2x).
 */
enum SizeVariantFormat: string
{
	// Thumbs are JPEG, small/medium keep the format of the original.
	case ORIGINAL = 'original';
	case JPEG = 'jpeg';
	case WEBP = 'webp';

	/**
	 * Extension (incl. the preceding dot) forced on generated size variants,
	 * or null if the extension depends on the original.
	 */
	public function extension(): ?string
	{
		return match ($this) {
			self::ORIGINAL => null,
			self::JPEG => '.jpeg',
			self::WEBP => '.webp',
		};
	}
}
