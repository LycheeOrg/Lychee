<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Enum\NsfwStatus;
use App\Http\Requests\Nsfw\BulkNsfwScanRequest;
use App\Http\Requests\Nsfw\NsfwDetectionResultsRequest;
use App\Models\Photo;
use App\Services\Image\NsfwActionService;
use App\Services\Image\NsfwDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class NsfwDetectionController extends Controller
{
	/**
	 * Receive NSFW detection results from the classification service.
	 *
	 * POST /api/v2/NsfwDetection/results
	 */
	public function results(NsfwDetectionResultsRequest $request, NsfwActionService $action_service): JsonResponse
	{
		$photo_id = $request->result->photo_id;
		$photo = Photo::find($photo_id);

		if ($photo === null) {
			Log::warning("NsfwDetectionController: photo {$photo_id} not found.");

			return response()->json(['status' => 'ok']);
		}

		if ($request->result->status === 'error') {
			$photo->nsfw_status = NsfwStatus::FAILED;
			$photo->save();
			Log::warning("NsfwDetectionController: NSFW scan error for photo {$photo_id}.", [
				'error_code' => $request->result->error_code,
				'message' => $request->result->message,
			]);

			return response()->json(['status' => 'ok']);
		}

		$action_service->logDetections(
			$photo_id,
			$request->result->block_detected,
			$request->result->review_detected,
			$request->result->sensitive_detected,
		);

		$action_service->applyActions(
			$photo,
			$request->result->should_block,
			$request->result->should_review,
			$request->result->is_sensitive,
		);

		return response()->json(['status' => 'ok']);
	}

	/**
	 * Trigger a bulk NSFW scan for unscanned photos.
	 *
	 * POST /api/v2/NsfwDetection/bulk-scan
	 */
	public function bulkScan(BulkNsfwScanRequest $request, NsfwDetectionService $service): Response
	{
		$service->dispatchUnscannedPhotos($request->albumId(), $request->force());

		return response()->noContent();
	}
}
