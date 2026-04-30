<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\FaceResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PaginatedFaceResource extends Data
{
	/** @var Collection<int,FaceResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.FaceResource[]')]
	public Collection $data;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param LengthAwarePaginator<FaceResource> $paginated_faces
	 */
	public function __construct(LengthAwarePaginator $paginated_faces)
	{
		$this->data = collect($paginated_faces->items())->map(fn ($face) => new FaceResource($face))->values();
		$this->current_page = $paginated_faces->currentPage();
		$this->last_page = $paginated_faces->lastPage();
		$this->per_page = $paginated_faces->perPage();
		$this->total = $paginated_faces->total();
	}
}
