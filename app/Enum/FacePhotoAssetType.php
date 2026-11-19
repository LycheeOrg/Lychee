<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Size variants served by `GET /api/v3/Faces/{face_id}/photo/{variant}`
 * (Feature 093, ADR-093-01). Unlike {@see SizeVariantAssetType}, `medium`
 * is allowed: only the photo owner or an admin reaches this route, so there
 * is no full-photo access split to protect.
 */
enum FacePhotoAssetType: string
{
	case MEDIUM = 'medium';
	case SMALL2X = 'small2x';
	case SMALL = 'small';
	case THUMB = 'thumb';

	/**
	 * Size variant types to try, best first, starting at this one.
	 *
	 * @return SizeVariantType[]
	 */
	public function candidates(): array
	{
		$chain = [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL, SizeVariantType::THUMB];
		$start = match ($this) {
			self::MEDIUM => 0,
			self::SMALL2X => 1,
			self::SMALL => 2,
			self::THUMB => 3,
		};

		return array_slice($chain, $start);
	}
}
