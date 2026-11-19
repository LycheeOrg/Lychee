<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\RecomputeBucketsRequest;
use App\Jobs\RecomputeAllAlbumBucketsJob;
use App\Jobs\RecomputeAllPhotoBucketsJob;
use Illuminate\Routing\Controller;

/**
 * Rewrites every album and `photo_album` bucket key.
 *
 * This controller is equivalent to running the artisan command:
 * php artisan lychee:recompute-buckets
 *
 * The two jobs run on the `low` queue (inline on `sync`) and flush the
 * album and photo listings once their keys are written.
 */
class RecomputeBuckets extends Controller
{
	/**
	 * Queue the rewrite of every bucket key.
	 *
	 * @param RecomputeBucketsRequest $_request
	 *
	 * @return void
	 */
	public function do(RecomputeBucketsRequest $_request): void
	{
		RecomputeAllAlbumBucketsJob::dispatch();
		RecomputeAllPhotoBucketsJob::dispatch();
	}
}
