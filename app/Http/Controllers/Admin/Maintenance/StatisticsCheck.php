<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Diagnostics\Pipes\Checks\StatisticsIntegrityCheck;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Resources\Diagnostics\StatisticsCheckResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * We may miss some statistics because of generation problem.
 * This module aims to solve this issue.
 */
class StatisticsCheck extends Controller
{
	public function __construct(
		private StatisticsIntegrityCheck $check,
	) {
	}

	/**
	 * Create th emissing statistics.
	 *
	 * @return StatisticsCheckResource
	 */
	public function do(MaintenanceRequest $request): StatisticsCheckResource
	{
		// Just skip the check, we don't care.
		if (!$request->configs()->getValueAsBool('metrics_enabled')) {
			return new StatisticsCheckResource(0, 0);
		}

		DB::statement('INSERT INTO statistics (photo_id) SELECT photos.id FROM photos LEFT OUTER JOIN statistics ON photos.id = photo_id WHERE statistics.id IS NULL');
		DB::statement('INSERT INTO statistics (album_id) SELECT base_albums.id FROM base_albums LEFT OUTER JOIN statistics ON base_albums.id = album_id WHERE statistics.id IS NULL');

		return $this->check->get();
	}

	/**
	 * Check how many statistics are missing for photos and albums.
	 *
	 * @return StatisticsCheckResource
	 */
	public function check(MaintenanceRequest $request): StatisticsCheckResource
	{
		return $this->check->get();
	}
}
