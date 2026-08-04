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
 * Admin maintenance controller to purge face embeddings from the AI Vision
 * service that no longer exist in Lychee (reverse of SyncFaceEmbeddings).
 *
 * GET  /Maintenance::purgeOrphanFaceEmbeddings — check: returns count of likely orphans
 * POST /Maintenance::purgeOrphanFaceEmbeddings — do: push all Lychee face IDs to AI Vision, then purge the rest
 */
class PurgeOrphanFaceEmbeddings extends Controller
{
	private const BATCH_SIZE = 500;

	/**
	 * Check: compare face counts between AI Vision and Lychee.
	 *
	 * Returns the number of embeddings potentially orphaned in the AI Vision
	 * service (0 if AI Vision does not hold more embeddings than Lychee).
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		if (!$request->configs()->getValueAsBool('ai_vision_enabled')) {
			return 0;
		}

		$service = app(FacialRecognitionService::class);

		try {
			$health = $service->checkHealth();
		} catch (\Throwable $e) {
			Log::warning('PurgeOrphanFaceEmbeddings::check — AI Vision service health check failed: ' . $e->getMessage());

			return 0;
		}

		$ai_count = $health['embedding_count'];
		$lychee_count = Face::count();

		return max(0, $ai_count - $lychee_count);
	}

	/**
	 * Do: push all Lychee face IDs to the AI Vision service in batches, marking
	 * them present, then purge anything left over that was not marked present.
	 *
	 * @return array{purged_count: int}
	 */
	public function do(MaintenanceRequest $_request): array
	{
		$service = app(FacialRecognitionService::class);

		$batch = 0;
		Face::query()->orderBy('id')->chunk(self::BATCH_SIZE, function ($faces) use ($service, &$batch): void {
			$service->syncEmbeddingsBatch($faces->pluck('id')->all(), $batch);
			$batch++;
		});

		// Ensure at least one batch is sent so an empty Lychee database still
		// resets the "present" flags in AI Vision before purging everything.
		if ($batch === 0) {
			$service->syncEmbeddingsBatch([], 0);
		}

		$purge = $service->purgeAbsentEmbeddings();

		if ($purge === null) {
			Log::warning('PurgeOrphanFaceEmbeddings::do — AI Vision service /embeddings/purge returned null.');

			return ['purged_count' => 0];
		}

		Log::info("PurgeOrphanFaceEmbeddings::do — purged {$purge['deleted']} orphaned embeddings from AI Vision.");

		return ['purged_count' => $purge['deleted']];
	}
}
