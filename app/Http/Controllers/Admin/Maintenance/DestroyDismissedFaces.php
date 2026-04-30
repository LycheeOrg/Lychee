<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Models\Face;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Admin maintenance controller to destroy all dismissed faces and their associated data.
 *
 * GET  /Maintenance::destroyDismissedFaces — check: returns count of dismissed faces
 * POST /Maintenance::destroyDismissedFaces — do: hard-delete dismissed faces and crop files
 */
class DestroyDismissedFaces extends Controller
{
	/**
	 * Check: return count of faces marked as dismissed.
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		if (!$request->configs()->getValueAsBool('ai_vision_enabled')) {
			return 0;
		}

		return Face::dismissed()->count();
	}

	/**
	 * Do: hard-delete all dismissed faces, their crop files, and their embeddings.
	 *
	 * @return array{deleted_count: int}
	 */
	public function do(MaintenanceRequest $_request): array
	{
		$dismissed_faces = Face::dismissed()->get();
		$face_ids = $dismissed_faces->pluck('id')->all();
		$count = 0;

		foreach ($dismissed_faces as $face) {
			if ($face->crop_token !== null) {
				$tok = $face->crop_token;
				$path = 'faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg';
				Storage::disk('images')->delete($path);
			}
			$face->delete();
			$count++;
		}

		if ($face_ids !== []) {
			DeleteFaceEmbeddingsJob::dispatch($face_ids);
		}

		return ['deleted_count' => $count];
	}
}
