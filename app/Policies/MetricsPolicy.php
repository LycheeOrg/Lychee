<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Enum\LiveMetricsAccess;
use App\Models\User;
use App\Repositories\ConfigManager;

class MetricsPolicy extends BasePolicy
{
	public const CAN_SEE_LIVE = 'canSeeLive';

	/**
	 * Check if the user can access the activlty activity metrics.
	 */
	public function canSeeLive(?User $user): bool
	{
		$config_manager = app(ConfigManager::class);
		$access_level = $config_manager->getValueAsEnum('live_metrics_access', LiveMetricsAccess::class);

		return match ($access_level) {
			LiveMetricsAccess::LOGGEDIN => true,
			LiveMetricsAccess::ADMIN => $user?->may_administrate === true,
			default => false,
		};
	}
}
