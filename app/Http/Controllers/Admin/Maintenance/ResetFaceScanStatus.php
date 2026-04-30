<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\FaceScanStatus;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

/**
 * Admin maintenance controller to reset photos with a stuck or failed face scan status.
 * Combines the functionality of resetting stuck-pending scans AND failed scans into one block.
 *
 * "Stuck" = face_scan_status = 'pending' AND updated_at older than DEFAULT_OLDER_THAN minutes.
 * "Failed" = face_scan_status = 'failed'.
 *
 * GET  /Maintenance::resetFaceScanStatus — check: returns combined count
 * POST /Maintenance::resetFaceScanStatus — do: resets both stuck and failed to null
 */
class ResetFaceScanStatus extends Controller
{
	/**
	 * Check: return combined count of stuck-pending and failed photos.
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		if (!$request->configs()->getValueAsBool('ai_vision_enabled')) {
			return 0;
		}

		$threshold_minutes = (int) config('features.ai-vision.face-stuck-scan-threshold-minutes', 720);
		$cutoff = Carbon::now()->subMinutes($threshold_minutes);

		$stuck_count = Photo::where('face_scan_status', '=', FaceScanStatus::PENDING->value)
			->where('updated_at', '<', $cutoff)
			->count();

		$failed_count = Photo::where('face_scan_status', '=', FaceScanStatus::FAILED->value)
			->count();

		return $stuck_count + $failed_count;
	}

	/**
	 * Do: reset both stuck-pending (older than threshold) and failed photos to null.
	 *
	 * @return array{reset_count: int}
	 */
	public function do(MaintenanceRequest $_request): array
	{
		$threshold_minutes = (int) config('features.ai-vision.face-stuck-scan-threshold-minutes', 720);
		$cutoff = Carbon::now()->subMinutes($threshold_minutes);

		// Reset failed scans
		$failed_count = Photo::where('face_scan_status', '=', FaceScanStatus::FAILED->value)
			->update(['face_scan_status' => null]);

		// Reset stuck-pending scans
		$stuck_count = Photo::where('face_scan_status', '=', FaceScanStatus::PENDING->value)
			->where('updated_at', '<', $cutoff)
			->update(['face_scan_status' => null]);

		return ['reset_count' => $failed_count + $stuck_count];
	}
}
