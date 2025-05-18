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
use Illuminate\Support\Carbon;

abstract class BaseMetricsEvent
{
	use Dispatchable;
	use InteractsWithSockets;

	public function __construct(
		public readonly string $visitor_id,
		public readonly string $id,
	) {
	}

	/**
	 * Return the type of key : photo_id or album_id.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore, abstract method can't be covered
	 */
	abstract public function key(): string;

	/**
	 * Return the column name in the database to update.
	 *
	 * @return MetricsAction
	 *
	 * @codeCoverageIgnore, abstract method can't be covered
	 */
	abstract public function metricAction(): MetricsAction;

	/**
	 * Convert the event to an array for insertion into the database.
	 *
	 * @return array<string,string|MetricsAction|Carbon>
	 */
	abstract public function toArray(): array;
}
