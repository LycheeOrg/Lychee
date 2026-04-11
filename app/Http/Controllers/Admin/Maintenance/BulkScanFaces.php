<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Services\FaceDetectionService;
use Illuminate\Routing\Controller;

/**
 * Admin maintenance controller to bulk scan photos for faces.
 *
 * GET  /Maintenance::bulkScanFaces — check if AI Vision is enabled and count unscannable photos
 * POST /Maintenance::bulkScanFaces — enqueue all unscanned photos for face detection
 */
class BulkScanFaces extends Controller
{
	/**
	 * Check if AI Vision is enabled and return count of photos to be scanned.
	 *
	 * @return int number of photos that can be scanned, or 0 if AI Vision is disabled
	 */
	public function check(MaintenanceRequest $request, FaceDetectionService $service): int
	{
		if (!$request->configs()->getValueAsBool('ai_vision_enabled')) {
			return 0;
		}

		return $service->countUnscanedPhotos();
	}

	/**
	 * Enqueue all unscanned photos for face detection.
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request, FaceDetectionService $service): void
	{
		$service->dispatchUnscanedPhotos();
	}
}
