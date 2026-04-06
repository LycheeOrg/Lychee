<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\FaceScanStatus;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Requests\Maintenance\ResetStuckFacesRequest;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

/**
 * Admin maintenance controller to detect and reset photos stuck in the 'pending'
 * face scan state (e.g. after a worker crash).
 *
 * GET  /Maintenance::resetStuckFaces — returns count of stuck records
 * POST /Maintenance::resetStuckFaces — resets stuck records back to null
 */
class ResetStuckFaces extends Controller
{
	/** @var int Default age in minutes before a pending scan is considered stuck */
	private const DEFAULT_OLDER_THAN = 720; // 12 hours

	/**
	 * Check: return count of photos stuck in 'pending' longer than the threshold.
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $_request): int
	{
		$cutoff = Carbon::now()->subMinutes(self::DEFAULT_OLDER_THAN);

		return Photo::where('face_scan_status', '=', FaceScanStatus::PENDING->value)
			->where('updated_at', '<', $cutoff)
			->count();
	}

	/**
	 * Do: reset stuck pending photos back to null and return the count reset.
	 *
	 * @return array{reset_count: int}
	 */
	public function do(ResetStuckFacesRequest $request): array
	{
		$cutoff = Carbon::now()->subMinutes($request->olderThanMinutes());

		$reset_count = Photo::where('face_scan_status', '=', FaceScanStatus::PENDING->value)
			->where('updated_at', '<', $cutoff)
			->update(['face_scan_status' => null]);

		return ['reset_count' => $reset_count];
	}
}
