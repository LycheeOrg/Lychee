<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

enum MetricsAction: string
{
	case VISIT = 'visit';
	case FAVOURITE = 'favourite';
	case DOWNLOAD = 'download';
	case SHARED = 'shared';

	/**
	 * Given a MetricsAction return the associated column name.
	 *
	 * @return string
	 */
	public function column(): string
	{
		return match ($this) {
			self::VISIT => 'visit_count',
			self::FAVOURITE => 'favourite_count',
			self::DOWNLOAD => 'download_count',
			self::SHARED => 'shared_count',
		};
	}
}
