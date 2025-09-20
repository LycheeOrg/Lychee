<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\Configs;
use App\Models\Purchasable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PurchasableResource extends Data
{
	public function __construct(
		public int $purchasable_id,
		public ?string $album_id,
		public ?string $photo_id,
		#[LiteralTypeScriptType('App.Http.Resources.Shop.PriceResource[]|null')]
		public array $prices,
		public ?string $owner_notes,
		public ?string $description,
		public bool $is_active,
	) {
	}

	/**
	 * @return PurchasableResource
	 */
	public static function fromModel(Purchasable $item): PurchasableResource
	{
		/** @var User|null $user */
		$user = Auth::user();

		return new self(
			purchasable_id: $item->id,
			album_id: $item->album_id,
			photo_id: $item->photo_id,
			prices: $item->prices->map(PriceResource::fromModel(...))->toArray(),
			owner_notes: $user?->may_administrate === true ? $item->owner_notes : null,
			description: $item->description ?? Configs::getValueAsString('webshopt_default_description'),
			is_active: $item->is_active,
		);
	}
}
