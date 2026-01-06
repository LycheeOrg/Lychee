<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

enum ConvertableImageType: string
{
	case HEIC = 'heic';
	case HEIF = 'heif';

	public static function isHeifImageType(string $extension): bool
	{
		$extension = str($extension)->lower()->toString();

		return in_array($extension, [
			self::HEIC->value,
			self::HEIF->value,
		], true);
	}
}