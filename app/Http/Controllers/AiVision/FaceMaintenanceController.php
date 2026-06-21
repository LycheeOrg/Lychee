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
use App\Models\Person;
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
		$dismissed_only = filter_var($request->query('dismissed_only', false), FILTER_VALIDATE_BOOLEAN) === true;

		$per_page = max(1, min(200, (int) ($request->query('per_page', 50))));

		$paginated = Face::with(['photo:id,title', 'person:id,name', 'suggestions'])
			->where('is_dismissed', '=', $dismissed_only)
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
		$affected_person_ids = Face::whereIn('id', $request->face_ids)
			->whereNotNull('person_id')
			->distinct()
			->pluck('person_id')
			->all();

		$count = Face::whereIn('id', $request->face_ids)
			->update(['is_dismissed' => true, 'person_id' => null]);

		foreach ($affected_person_ids as $person_id) {
			$person = Person::find($person_id);
			if ($person === null) {
				continue;
			}

			$person->face_count = Face::where('person_id', '=', $person_id)->where('is_dismissed', '=', false)->count();
			if ($person->face_count === 0) {
				$person->delete();
				continue;
			}

			$person->photo_count = Face::where('person_id', '=', $person_id)->where('is_dismissed', '=', false)->distinct('photo_id')->count('photo_id');
			$person->save();
		}

		return ['dismissed_count' => $count];
	}
}

