<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ModerationResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PaginatedModerationResource extends Data
{
	/** @var Collection<int,ModerationResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ModerationResource[]')]
	public Collection $photos;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param ?LengthAwarePaginator<\App\Models\Photo> $paginated_photos
	 */
	public function __construct(
		?LengthAwarePaginator $paginated_photos,
	) {
		$items = collect($paginated_photos?->items() ?? []);
		$this->photos = $items->map(fn ($photo) => new ModerationResource($photo));
		$this->current_page = $paginated_photos?->currentPage() ?? 1;
		$this->last_page = $paginated_photos?->lastPage() ?? 1;
		$this->per_page = $paginated_photos?->perPage() ?? 0;
		$this->total = $paginated_photos?->total() ?? 0;
	}
}
