<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Face\ClusterAssignRequest;
use App\Http\Requests\Face\ClusterDismissRequest;
use App\Http\Requests\Face\ClusterIndexRequest;
use App\Http\Resources\Models\ClusterPreviewResource;
use App\Models\Face;
use App\Models\Person;
use App\Repositories\ConfigManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class FaceClusterController extends Controller
{
	private const PER_PAGE = 20;
	private const SAMPLE_SIZE = 5;

	public function index(ClusterIndexRequest $request): LengthAwarePaginator
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

		return new LengthAwarePaginator($items, $total, self::PER_PAGE, $page, ['path' => $request->url()]);
	}

	public function assign(ClusterAssignRequest $request, int $label): array
	{
		if ($request->personId() !== null) {
			$person_id = $request->personId();
		} else {
			$is_searchable_default = app(ConfigManager::class)->getValueAsBool('ai_vision_face_person_is_searchable_default');
			$person = new Person();
			$person->name = (string) $request->newPersonName();
			$person->is_searchable = $is_searchable_default;
			$person->save();
			$person_id = $person->id;
		}
		$count = Face::where('cluster_label', '=', $label)
			->whereNull('person_id')
			->where('is_dismissed', '=', false)
			->update(['person_id' => $person_id]);

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
