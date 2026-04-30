<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Models\Face;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Admin maintenance controller to synchronize face embeddings from AI Vision service.
 *
 * GET  /Maintenance::syncFaceEmbeddings — check: returns count mismatch (0 if in sync)
 * POST /Maintenance::syncFaceEmbeddings — do: pull all embeddings from AI Vision and update Lychee faces
 */
class SyncFaceEmbeddings extends Controller
{
	/**
	 * Check: compare face counts between Lychee and AI Vision service.
	 *
	 * Returns the absolute difference (0 if in sync).
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		if (!$request->configs()->getValueAsBool('ai_vision_enabled')) {
			return 0;
		}

		$service = app(FacialRecognitionService::class);
		$health = $service->checkHealth();

		if ($health === null) {
			Log::warning('SyncFaceEmbeddings::check — AI Vision service /health returned null.');

			return 0;
		}

		$lychee_count = Face::count();
		$ai_count = $health['embedding_count'];

		return abs($lychee_count - $ai_count);
	}

	/**
	 * Do: synchronize all face embeddings from AI Vision service.
	 *
	 * Updates existing faces with latest metadata, preserving is_dismissed flag.
	 *
	 * @return array{synced_count: int, missing_in_ai: int}
	 */
	public function do(MaintenanceRequest $_request): array
	{
		$service = app(FacialRecognitionService::class);
		$export = $service->syncFaceEmbeddings();

		if ($export === null) {
			Log::warning('SyncFaceEmbeddings::do — AI Vision service /embeddings/export returned null.');

			return ['synced_count' => 0, 'missing_in_ai' => 0];
		}

		$ai_face_ids = [];
		$synced = 0;

		foreach ($export['embeddings'] as $item) {
			$face_id = $item['lychee_face_id'];
			$ai_face_ids[] = $face_id;

			// Update or create face record (preserving is_dismissed if it exists)
			$face = Face::find($face_id);

			if ($face !== null) {
				// Update existing face, keeping is_dismissed flag
				$face->photo_id = $item['photo_id'];
				$face->laplacian_variance = $item['laplacian_variance'];
				$face->save();
				$synced++;
			} else {
				Log::warning("SyncFaceEmbeddings::do — Face {$face_id} exists in AI Vision but not in Lychee database.");
			}
		}

		// Count faces in Lychee that are missing in AI Vision
		$lychee_count = Face::count();
		$missing_in_ai = $lychee_count - count($ai_face_ids);

		Log::info("SyncFaceEmbeddings::do — synced {$synced} faces, {$missing_in_ai} in Lychee but not in AI Vision.");

		return ['synced_count' => $synced, 'missing_in_ai' => max(0, $missing_in_ai)];
	}
}
