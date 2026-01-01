<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Metrics;

use App\Models\LiveMetrics;
use App\Repositories\ConfigManager;

class CleanupMetrics
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function do(): void
	{
		$num_days = $this->config_manager->getValueAsInt('live_metrics_max_time');

		LiveMetrics::query()->where('created_at', '<=', now()->subDays($num_days))->delete();
	}
}
