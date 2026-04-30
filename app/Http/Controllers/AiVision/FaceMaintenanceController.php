<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Face\BatchDismissFacesRequest;
use App\Http\Requests\Face\FaceMaintenanceIndexRequest;
use App\Http\Resources\Collections\PaginatedFaceResource;
use App\Models\Face;
use Illuminate\Routing\Controller;

/**
 * Controller for the Face Maintenance admin page.
 */
class FaceMaintenanceController extends Controller
{
	/**
	 * Return a paginated list of all faces for admin quality review.
	 *
	 * GET /Face/maintenance
	 *
	 * Supports query params:
	 *   sort_by  – 'confidence' (default) or 'laplacian_variance'
	 *   sort_dir – 'asc' (default) or 'desc'
	 *   page     – page number
	 *   per_page – items per page (default 50)
	 */
	public function index(FaceMaintenanceIndexRequest $request): PaginatedFaceResource
	{
		$sort_by = in_array($request->query('sort_by'), ['confidence', 'laplacian_variance'], true)
			? $request->query('sort_by')
			: 'confidence';

		$sort_dir = $request->query('sort_dir') === 'desc' ? 'desc' : 'asc';

		$per_page = max(1, min(200, (int) ($request->query('per_page', 50))));

		$paginated = Face::with(['photo:id,title', 'person:id,name', 'suggestions'])
			->orderBy($sort_by, $sort_dir)
			->paginate($per_page);

		return new PaginatedFaceResource($paginated);
	}

	/**
	 * Batch-dismiss multiple faces.
	 *
	 * POST /Face/maintenance/batch-dismiss
	 *
	 * @return array{dismissed_count: int}
	 */
	public function batchDismiss(BatchDismissFacesRequest $request): array
	{
		$count = Face::whereIn('id', $request->face_ids)
			->update(['is_dismissed' => true]);

		return ['dismissed_count' => $count];
	}
}

