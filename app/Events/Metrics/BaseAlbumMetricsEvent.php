<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;
use Illuminate\Support\Carbon;

abstract class BaseAlbumMetricsEvent extends BaseMetricsEvent
{
	/**
	 * Return the type of key : photo_id or album_id.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore, abstract method can't be covered
	 */
	final public function key(): string
	{
		return 'album_id';
	}

	/**
	 * Convert the event to an array for insertion into the database.
	 *
	 * @return array{visitor_id:string,action:MetricsAction,album_id:string,created_at:Carbon}
	 */
	final public function toArray(): array
	{
		return [
			'visitor_id' => $this->visitor_id,
			'action' => $this->metricAction(),
			'album_id' => $this->id,
			'created_at' => now(),
		];
	}
}
