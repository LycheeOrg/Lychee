<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ContactMessageResource;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ContactMessageCollectionResource extends Data
{
	/** @var Collection<int,ContactMessageResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ContactMessageResource[]')]
	public Collection $data;

	public int $current_page;
	public int $per_page;
	public int $total;

	/**
	 * @param Collection<int,ContactMessageResource> $data
	 */
	public function __construct(Collection $data, int $total, int $per_page, int $current_page)
	{
		$this->data = $data;
		$this->total = $total;
		$this->per_page = $per_page;
		$this->current_page = $current_page;
	}
}
