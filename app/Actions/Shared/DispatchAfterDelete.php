<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shared;

use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Dispatches the jobs a delete action starts after its DB commit (FR-112-04).
 *
 * On the `sync` connection the jobs would otherwise run inside the HTTP
 * request: they are deferred until the response has been sent. In console
 * they run when the command exits. This is best effort: the jobs only live
 * in memory, so a worker killed before the list is done loses the rest.
 *
 * Any other connection stores each job when it is dispatched, as usual.
 */
final class DispatchAfterDelete
{
	/**
	 * @param iterable<ShouldQueue> $jobs
	 */
	public static function dispatch(iterable $jobs): void
	{
		$after_response = config('queue.default') === 'sync';
		foreach ($jobs as $job) {
			if ($after_response) {
				dispatch($job)->afterResponse();
			} else {
				dispatch($job);
			}
		}
	}
}
