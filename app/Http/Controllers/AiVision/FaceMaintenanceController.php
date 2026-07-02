<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Factories\PersonFactory;
use App\Http\Requests\Face\BatchAssignFacesRequest;
use App\Http\Requests\Face\BatchDismissFacesRequest;
use App\Http\Requests\Face\FaceMaintenanceIndexRequest;
use App\Http\Resources\Collections\PaginatedFaceResource;
use App\Jobs\RecomputePersonStatsJob;
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
	 */
	public function index(FaceMaintenanceIndexRequest $request): PaginatedFaceResource
	{
		$query = Face::with(['photo:id,title', 'person:id,name', 'suggestions'])
			->where('is_dismissed', '=', $request->dismissed_only);

		if ($request->unassigned_only) {
			$query->whereNull('person_id');
		}

		$paginated = $query->orderBy($request->sort_by, $request->sort_dir->value)
			->paginate($request->per_page);

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

		RecomputePersonStatsJob::dispatchSync($affected_person_ids);

		return ['dismissed_count' => $count];
	}

	/**
	 * Batch-assign multiple faces to an existing person or a newly created one.
	 *
	 * POST /Face/maintenance/batch-assign
	 *
	 * @return array{assigned_count: int, person_id: string}
	 */
	public function batchAssign(BatchAssignFacesRequest $request, PersonFactory $person_factory): array
	{
		$person = $person_factory->findOrCreate($request->person_id, $request->new_person_name);

		$old_person_ids = Face::whereIn('id', $request->face_ids)
			->whereNotNull('person_id')
			->where('person_id', '!=', $person->id)
			->distinct()
			->pluck('person_id')
			->all();

		$count = Face::whereIn('id', $request->face_ids)->update(['person_id' => $person->id]);

		RecomputePersonStatsJob::dispatchSync([$person->id, ...$old_person_ids]);

		return ['assigned_count' => $count, 'person_id' => $person->id];
	}
}

