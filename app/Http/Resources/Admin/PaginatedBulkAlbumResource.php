<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Paginated collection of BulkAlbumResource rows.
 */
#[TypeScript()]
class PaginatedBulkAlbumResource extends Data
{
	/** @var Collection<int,BulkAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Admin.BulkAlbumResource[]')]
	public Collection $data;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param LengthAwarePaginator<BulkAlbumResource> $paginator
	 */
	public function __construct(LengthAwarePaginator $paginator)
	{
		$this->data = collect($paginator->items());
		$this->current_page = $paginator->currentPage();
		$this->last_page = $paginator->lastPage();
		$this->per_page = $paginator->perPage();
		$this->total = $paginator->total();
	}
}
