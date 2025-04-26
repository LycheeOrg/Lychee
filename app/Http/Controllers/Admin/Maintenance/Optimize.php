<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Db\OptimizeDb;
use App\Actions\Db\OptimizeTables;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use Illuminate\Routing\Controller;

/**
 * This modules takes care of the optimization of the Database.
 */
class Optimize extends Controller
{
	/**
	 * Apply the indexing and optimization of the database.
	 *
	 * @return string[]
	 *
	 * @codeCoverageIgnore
	 */
	public function do(MaintenanceRequest $request, OptimizeDb $optimize_db, OptimizeTables $optimize_tables): array
	{
		return collect($optimize_db->do())
			->merge(collect($optimize_tables->do()))
			->all();
	}
}
