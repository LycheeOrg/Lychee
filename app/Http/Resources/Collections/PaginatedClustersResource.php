<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ClusterPreviewResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PaginatedClustersResource extends Data
{
	/** @var Collection<int,ClusterPreviewResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ClusterPreviewResource[]')]
	public Collection $data;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param LengthAwarePaginator<ClusterPreviewResource> $paginated_clusters
	 */
	public function __construct(LengthAwarePaginator $paginated_clusters)
	{
		$this->data = collect($paginated_clusters->items());
		$this->current_page = $paginated_clusters->currentPage();
		$this->last_page = $paginated_clusters->lastPage();
		$this->per_page = $paginated_clusters->perPage();
		$this->total = $paginated_clusters->total();
	}
}
