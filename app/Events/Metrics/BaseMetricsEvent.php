<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

abstract class BaseMetricsEvent
{
	use Dispatchable;
	use InteractsWithSockets;

	public function __construct(
		public readonly string $visitor_id,
		public readonly string $id,
	) {
	}

	abstract public function key(): string;

	abstract public function metricAction(): MetricsAction;
}
