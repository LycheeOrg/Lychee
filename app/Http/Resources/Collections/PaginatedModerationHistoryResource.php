<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ModerationEventResource;
use App\Models\PhotoModerationEvent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A page of the moderation history (Feature 089, FR-089-05).
 */
#[TypeScript()]
class PaginatedModerationHistoryResource extends Data
{
	/** @var Collection<int,ModerationEventResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ModerationEventResource[]')]
	public Collection $events;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param LengthAwarePaginator<int,PhotoModerationEvent> $paginated_events
	 */
	public function __construct(LengthAwarePaginator $paginated_events)
	{
		$this->events = collect($paginated_events->items())->map(fn (PhotoModerationEvent $event) => new ModerationEventResource($event));
		$this->current_page = $paginated_events->currentPage();
		$this->last_page = $paginated_events->lastPage();
		$this->per_page = $paginated_events->perPage();
		$this->total = $paginated_events->total();
	}
}
