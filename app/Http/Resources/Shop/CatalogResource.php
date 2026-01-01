<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\Purchasable;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class CatalogResource extends Data
{
	public ?PurchasableResource $album_purchasable;
	/** @var Collection<int,PurchasableResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Shop.PurchasableResource[]')]
	public Collection $children_purchasables;
	/** @var Collection<int,PurchasableResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Shop.PurchasableResource[]')]
	public Collection $photo_purchasables;

	public function __construct(
		?Purchasable $album_purchasable,
		Collection $children_purchasables,
		Collection $photo_purchasables,
	) {
		$this->album_purchasable = $album_purchasable !== null ? PurchasableResource::fromModel($album_purchasable) : null;
		$this->children_purchasables = PurchasableResource::collect($children_purchasables, Collection::class);
		$this->photo_purchasables = PurchasableResource::collect($photo_purchasables, Collection::class);
	}
}
