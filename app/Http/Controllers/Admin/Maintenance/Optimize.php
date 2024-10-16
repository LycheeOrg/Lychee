<?php

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
	 */
	public function do(MaintenanceRequest $request, OptimizeDb $optimizeDb, OptimizeTables $optimizeTables): array
	{
		return collect($optimizeDb->do())
			->merge(collect($optimizeTables->do()))
			->all();
	}
}
