<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * This allows to check the state of the album tree.
 * It counts the number of inconsistencies like:
 * - albums with oddness,
 * - albums with duplicate parents,
 * - albums with wrong parents,
 * - albums with missing parents.
 *
 * We cache the result for 1 day to avoid performance issues.
 * No need to do that check more often.
 */
class CheckTreeState implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	/**
	 * Execute the job.
	 *
	 * @return array{oddness?:int,duplicates?:int,wrong_parent?:int,missing_parent?:int}
	 */
	public function handle(): array
	{
		return Cache::remember('tree_state', 24 * 3600, fn () => $this->countErrors()); // 1 day cache
	}

	/**
	 * Counts the errors in the album tree.
	 *
	 * @return array{oddness?:int,duplicates?:int,wrong_parent?:int,missing_parent?:int}
	 */
	private function countErrors(): array
	{
		return Album::query()->countErrors();
	}
}