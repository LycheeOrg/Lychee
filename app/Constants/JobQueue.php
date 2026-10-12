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
 * `php artisan queue:work --queue=default,low` only runs a `low` job once
 * `default` is empty.
 */
class JobQueue
{
	// Recompute jobs: run after the waiting uploads and imports.
	public const LOW = 'low';
}
