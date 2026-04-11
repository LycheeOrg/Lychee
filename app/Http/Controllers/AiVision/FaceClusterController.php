<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Factories\PersonFactory;
use App\Http\Requests\Face\ClusterAssignRequest;
use App\Http\Requests\Face\ClusterDismissRequest;
use App\Http\Requests\Face\ClusterFacesRequest;
use App\Http\Requests\Face\ClusterIndexRequest;
use App\Http\Requests\Face\UnclusterFacesRequest;
use App\Http\Resources\Collections\PaginatedClustersResource;
use App\Http\Resources\Collections\PaginatedFaceResource;
use App\Http\Resources\Models\ClusterPreviewResource;
use App\Models\Face;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class FaceClusterController extends Controller
{
	private const PER_PAGE = 20;
	private const SAMPLE_SIZE = 5;

	public function index(ClusterIndexRequest $request): PaginatedClustersResource
	{
		$page = (int) $request->query('page', '1');
		$totals = Face::query()
			->whereNotNull('cluster_label')
			->where('cluster_label', '>', -1)
			->whereNull('person_id')
			->notDismissed()
			->selectRaw('cluster_label, COUNT(*) as face_count')
			->groupBy('cluster_label')
			->orderBy('face_count', 'desc')
			->get();
		$total = $totals->count();
		$paginated = $totals->forPage($page, self::PER_PAGE);

		// Prefetch sample faces for all clusters in the paginated set to avoid N+1
		$cluster_labels = $paginated->pluck('cluster_label')->all();
		$samples_by_cluster = Face::query()
			->whereIn('cluster_label', $cluster_labels)
			->whereNull('person_id')
			->notDismissed()
			->whereNotNull('crop_token')
			->select('cluster_label', 'crop_token', 'confidence')
			->orderBy('cluster_label')
			->orderByDesc('confidence')
			->get()
			->groupBy('cluster_label')
			->map(fn ($faces) => $faces->take(self::SAMPLE_SIZE)->pluck('crop_token')->map(
				static fn ($tok) => 'uploads/faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg'
			)->all());

		$items = $paginated->map(function (Face $row) use ($samples_by_cluster): ClusterPreviewResource {
			$cluster_label = (int) $row->cluster_label;
			$face_count = (int) $row->face_count; // @phpstan-ignore property.notFound (see line 34)
			$samples = $samples_by_cluster->get($cluster_label, []);

			return new ClusterPreviewResource($cluster_label, $face_count, $samples);
		});

		$paginator = new LengthAwarePaginator($items, $total, self::PER_PAGE, $page, ['path' => $request->url()]);

		return new PaginatedClustersResource($paginator);
	}

	public function assign(ClusterAssignRequest $request, int $label, PersonFactory $person_factory): array
	{
		$person = $person_factory->findOrCreate($request->person_id, $request->new_person_name);
		$count = Face::where('cluster_label', '=', $label)
			->whereNull('person_id')
			->notDismissed()
			->update(['person_id' => $person->id]);

		return ['assigned_count' => $count];
	}

	public function dismiss(ClusterDismissRequest $request, int $label): array
	{
		$count = Face::where('cluster_label', '=', $label)
			->whereNull('person_id')
			->notDismissed()
			->update(['is_dismissed' => true]);

		return ['dismissed_count' => $count];
	}

	/**
	 * Remove selected faces from a cluster by setting cluster_label = NULL.
	 * Only affects qualifying faces (cluster_label = label, person_id IS NULL, is_dismissed = false).
	 *
	 * POST /FaceDetection/clusters/{label}/uncluster
	 *
	 * @return array{unclustered_count: int}
	 */
	public function uncluster(UnclusterFacesRequest $request, int $label): array
	{
		$count = Face::whereIn('id', $request->face_ids)
			->where('cluster_label', '=', $label)
			->whereNull('person_id')
			->notDismissed()
			->update(['cluster_label' => null]);

		return ['unclustered_count' => $count];
	}

	/**
	 * List the faces belonging to a cluster (unassigned, not dismissed).
	 *
	 * GET /FaceDetection/clusters/{label}/faces
	 */
	public function faces(ClusterFacesRequest $request, int $label): PaginatedFaceResource
	{
		$exists = Face::where('cluster_label', '=', $label)
			->whereNull('person_id')
			->notDismissed()
			->exists();

		if (!$exists) {
			abort(404, 'Cluster not found or has no qualifying faces.');
		}

		$paginated = Face::query()
			->where('cluster_label', '=', $label)
			->whereNull('person_id')
			->notDismissed()
			->with(['person', 'suggestions.suggestedFace.person'])
			->orderByDesc('confidence')
			->paginate(50);

		return new PaginatedFaceResource($paginated);
	}
}
