<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Contracts\Events\HasMetricAction;
use App\Contracts\Events\HasTable;
use App\Enum\MetricsAction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PhotoFavourite implements HasTable, HasMetricAction
{
	use Dispatchable;
	use InteractsWithSockets;

	/**
	 * This event is fired when a photo is visited.
	 *
	 * @return void
	 */
	public function __construct(
		public string $visitor_id,
		public string $id,
	) {
	}

	public function table(): string
	{
		return 'photos';
	}

	public function metricAction(): MetricsAction
	{
		return MetricsAction::FAVOURITE;
	}
}
