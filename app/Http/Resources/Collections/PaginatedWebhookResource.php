<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\WebhookResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PaginatedWebhookResource extends Data
{
	/** @var Collection<int,WebhookResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.WebhookResource[]')]
	public Collection $webhooks;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param ?LengthAwarePaginator<\App\Models\Webhook> $paginated_webhooks
	 */
	public function __construct(
		?LengthAwarePaginator $paginated_webhooks,
	) {
		$items = collect($paginated_webhooks?->items() ?? []);
		$this->webhooks = $items->map(fn ($webhook) => new WebhookResource($webhook));
		$this->current_page = $paginated_webhooks?->currentPage() ?? 1;
		$this->last_page = $paginated_webhooks?->lastPage() ?? 1;
		$this->per_page = $paginated_webhooks?->perPage() ?? 0;
		$this->total = $paginated_webhooks?->total() ?? 0;
	}
}
