<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

/**
 * Sometimes the job history is a bit messed up,
 * this happens when there are crashes or error in the logic.
 *
 * In theory this should not be needed but if this is not resolved
 * the pulse feedback would always stay alive.
 */
class FlushCache extends Controller
{
	/**
	 * Flush all the caches.
	 *
	 * @param MaintenanceRequest $_request
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $_request): void
	{
		Cache::flush();
	}
}
