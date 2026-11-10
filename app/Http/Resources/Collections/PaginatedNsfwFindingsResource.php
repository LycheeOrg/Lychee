<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\NsfwFindingResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A page of NSFW findings, one entry per photo (Feature 089, FR-089-09).
 */
#[TypeScript()]
class PaginatedNsfwFindingsResource extends Data
{
	/** @var Collection<int,NsfwFindingResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.NsfwFindingResource[]')]
	public Collection $photos;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param LengthAwarePaginator<int,object>    $paginated page of photo ids
	 * @param Collection<int,NsfwFindingResource> $photos    the entries of the page, in page order
	 */
	public function __construct(LengthAwarePaginator $paginated, Collection $photos)
	{
		$this->photos = $photos;
		$this->current_page = $paginated->currentPage();
		$this->last_page = $paginated->lastPage();
		$this->per_page = $paginated->perPage();
		$this->total = $paginated->total();
	}
}
