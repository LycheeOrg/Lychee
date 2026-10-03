<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Metrics;

use App\Enum\LiveMetricsCleanup;
use App\Models\LiveMetrics;
use App\Repositories\ConfigManager;
use function Safe\strtotime;

class CleanupMetrics
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Deletes the expired live metrics now, after the response, or never,
	 * depending on `live_metrics_cleanup` (Feature 079, FR-079-08).
	 */
	public function apply(): void
	{
		$mode = $this->config_manager->getValueAsEnum('live_metrics_cleanup', LiveMetricsCleanup::class);

		if ($mode === LiveMetricsCleanup::SYNC) {
			$this->do();
		}

		if ($mode === LiveMetricsCleanup::DEFERRED) {
			app()->terminating(fn () => $this->do());
		}
	}

	public function do(): void
	{
		LiveMetrics::query()->where('created_at', '<=', $this->threshold())->delete();
	}

	/**
	 * Live metrics created at or before this datetime (`Y-m-d H:i:s`) are expired.
	 */
	public function threshold(): string
	{
		$num_days = $this->config_manager->getValueAsInt('live_metrics_max_time');

		return date('Y-m-d H:i:s', strtotime('-' . $num_days . ' days'));
	}
}
