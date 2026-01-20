<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Resources\Diagnostics\TreeState;
use App\Jobs\CheckTreeState;
use Illuminate\Routing\Controller;

/**
 * Maybe the album tree is broken.
 * We fix it here.
 */
class FixTree extends Controller
{
	/**
	 * Check whether the Album tree is correct.
	 *
	 * @return TreeState
	 */
	public function check(MaintenanceRequest $request): TreeState
	{
		$check = new CheckTreeState();
		$stats = $check->handle();

		return new TreeState(
			$stats['oddness'] ?? 0,
			$stats['duplicates'] ?? 0,
			$stats['wrong_parent'] ?? 0,
			$stats['missing_parent'] ?? 0
		);
	}
}
