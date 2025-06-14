<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Flow;

use App\Models\Album;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class FlowResource extends Data
{
	/** @var array<int,FlowItemResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Flow.FlowItemResource[]')]
	public array $albums;

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	/**
	 * @param LengthAwarePaginator<int,FlowItemResource>&Paginator<int,FlowItemResource> $albums
	 *
	 * @return void
	 */
	public function __construct(
		LengthAwarePaginator $albums,
	) {
		$this->albums = $albums->items();
		$this->current_page = $albums->currentPage();
		$this->from = $albums->firstItem() ?? 0;
		$this->last_page = $albums->lastPage();
		$this->per_page = $albums->perPage();
		$this->to = $albums->lastItem() ?? 0;
		$this->total = $albums->total();
	}

	/**
	 * @param LengthAwarePaginator<int,Album> $albums
	 *
	 * @return FlowResource
	 */
	public static function fromData(LengthAwarePaginator $albums): self
	{
		/** @disregard Undefined method withQueryString() (stupid intelephense) */
		return new self(
			/** @phpstan-ignore method.notFound (this methods exists, it's in the doc...) */
			albums: $albums->through(fn ($p) => new FlowItemResource($p)),
		);
	}
}