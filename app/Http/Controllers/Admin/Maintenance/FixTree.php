<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Resources\Diagnostics\TreeState;
use App\Models\Album;
use Illuminate\Routing\Controller;

/**
 * Maybe the album tree is broken.
 * We fix it here.
 */
class FixTree extends Controller
{
	/**
	 * Clean the path from all files excluding $this->skip.
	 *
	 * @return int
	 */
	public function do(MaintenanceRequest $request): int
	{
		return Album::query()->fixTree();
	}

	/**
	 * Check whether there are files to be removed.
	 * If not, we will not display the module to reduce complexity.
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
