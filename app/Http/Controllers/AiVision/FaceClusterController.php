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
use App\Http\Requests\Face\ClusterIndexRequest;
use App\Http\Resources\Collections\PaginatedClustersResource;
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
			->where('is_dismissed', '=', false)
			->selectRaw('cluster_label, COUNT(*) as face_count')
			->groupBy('cluster_label')
			->orderBy('face_count', 'desc')
			->get();
		$total = $totals->count();
		$paginated = $totals->forPage($page, self::PER_PAGE);
		$items = $paginated->map(function (Face $row): ClusterPreviewResource {
			$cluster_label = (int) $row->cluster_label;
			$face_count = (int) $row->face_count; // @phpstan-ignore property.notFound (see line 34)
			// This is a n+1 query ...
			$samples = Face::query()
				->where('cluster_label', '=', $cluster_label)
				->whereNull('person_id')
				->where('is_dismissed', '=', false)
				->whereNotNull('crop_token')
				->orderByDesc('confidence')
				->limit(self::SAMPLE_SIZE)
				->pluck('crop_token')
				->map(static fn ($tok) => 'uploads/faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg')
				->all();

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
			->where('is_dismissed', '=', false)
			->update(['person_id' => $person->id]);

		return ['assigned_count' => $count];
	}

	public function dismiss(ClusterDismissRequest $request, int $label): array
	{
		$count = Face::where('cluster_label', '=', $label)
			->whereNull('person_id')
			->where('is_dismissed', '=', false)
			->update(['is_dismissed' => true]);

		return ['dismissed_count' => $count];
	}
}
