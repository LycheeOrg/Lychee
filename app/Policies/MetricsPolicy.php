<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Enum\LiveMetricsAccess;
use App\Models\User;

class MetricsPolicy extends BasePolicy
{
	public const CAN_SEE_LIVE = 'canSeeLive';

	/**
	 * Check if the user can access the activlty activity metrics.
	 */
	public function canSeeLive(?User $user): bool
	{
		$access_level = $this->config_manager->getValueAsEnum('live_metrics_access', LiveMetricsAccess::class);

		return match ($access_level) {
			LiveMetricsAccess::LOGGEDIN => true,
			LiveMetricsAccess::ADMIN => $user?->may_administrate === true,
			default => false,
		};
	}
}
