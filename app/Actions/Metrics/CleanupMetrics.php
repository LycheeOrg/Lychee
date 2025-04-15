<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Metrics;

use App\Models\Configs;
use App\Models\LiveMetrics;

class CleanupMetrics
{
	public function do(): void
	{
		$num_days = Configs::getValueAsInt('live_metrics_max_time');

		LiveMetrics::query()->where('created_at', '<=', now()->subDays($num_days))->delete();
	}
}
