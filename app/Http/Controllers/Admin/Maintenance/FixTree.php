<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Admin\Maintenance\Model\Album;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Resources\Diagnostics\TreeState;
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
		$stats = Album::query()->countErrors();

		return new TreeState(
			$stats['oddness'] ?? 0,
			$stats['duplicates'] ?? 0,
			$stats['wrong_parent'] ?? 0,
			$stats['missing_parent'] ?? 0
		);
	}
}
