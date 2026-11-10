<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Constants;

/**
 * Named queues the jobs are pushed on, next to the connection's default queue.
 *
 * Workers drain the queues in the order given to `--queue`:
 * `php artisan queue:work --queue=high,default,low` only runs a `default` job
 * once `high` is empty, and a `low` job once both are.
 */
class JobQueue
{
	// Drained first; no job is pushed on it.
	public const HIGH = 'high';

	// Recompute jobs: run after the waiting uploads and imports.
	public const LOW = 'low';
}
