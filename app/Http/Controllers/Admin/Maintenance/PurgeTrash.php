<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Photo\PurgeTrash as PurgeTrashAction;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Models\Scopes\NotTrashedScope;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Feature 119 (FR-119-05, API-119-01/02): pending deletions.
 *
 * Lets an admin finish a purge that could not run to the end, for example
 * on `sync` without a queue worker.
 */
class PurgeTrash extends Controller
{
	/**
	 * Number of photos waiting in the trash.
	 */
	public function check(MaintenanceRequest $request): int
	{
		return DB::table(NotTrashedScope::TABLE)->count();
	}

	/**
	 * Purge until the trash is empty, `maintenance_processing_limit` photos
	 * are done, or the 10 s budget is used.
	 */
	public function do(MaintenanceRequest $request, PurgeTrashAction $purge): void
	{
		$purge->run(
			$request->configs()->getValueAsInt('maintenance_processing_limit'),
			(float) PurgeTrashAction::SYNC_TIME_BUDGET,
		);
	}
}
