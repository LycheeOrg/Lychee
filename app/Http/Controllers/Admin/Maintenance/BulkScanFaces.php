<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\FaceScanStatus;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Jobs\DispatchFaceScanJob;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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
	public function check(MaintenanceRequest $request): int
	{
		if (!$request->configs()->getValueAsBool('ai_vision_enabled')) {
			return 0;
		}

		return Photo::query()
			->select('id')
			->whereNull('face_scan_status')
			->orWhere('face_scan_status', '=', FaceScanStatus::FAILED->value)
			->count();
	}

	/**
	 * Enqueue all unscanned photos for face detection.
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$batch_size = $request->configs()->getValueAsInt('ai_vision_face_scan_batch_size');

		$query = Photo::query()
			->select('id')
			->whereNull('face_scan_status')
			->orWhere('face_scan_status', '=', FaceScanStatus::FAILED->value);

		$dispatched = 0;
		$query->lazyById($batch_size, 'id')->chunk($batch_size)->each(function ($chunk) use (&$dispatched): void {
			$ids = $chunk->pluck('id')->all();
			Photo::whereIn('id', $ids)->update(['face_scan_status' => FaceScanStatus::PENDING->value]);

			foreach ($ids as $photo_id) {
				DispatchFaceScanJob::dispatch($photo_id);
				$dispatched++;
			}
		});

		Log::info("BulkScanFaces::do — dispatched {$dispatched} scans.");
	}
}
