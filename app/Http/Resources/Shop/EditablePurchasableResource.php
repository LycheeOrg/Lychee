<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\Purchasable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class EditablePurchasableResource extends Data
{
	public function __construct(
		public int $purchasable_id,
		public ?string $album_id,
		public ?string $album_title,
		public ?string $photo_id,
		public ?string $photo_title,
		public ?string $photo_url,
		#[LiteralTypeScriptType('App.Http.Resources.Shop.PriceResource[]|null')]
		public array $prices,
		public ?string $owner_notes,
		public ?string $description,
		public bool $is_active,
	) {
	}

	/**
	 * @return EditablePurchasableResource
	 */
	public static function fromModel(Purchasable $item): EditablePurchasableResource
	{
		$album_title = $item->relationLoaded('album') ? $item->album?->title : null;
		$photo_title = null;
		$photo_url = null;
		if ($item->relationLoaded('photo') && $item->photo !== null) {
			$photo_title = $item->photo?->title;
			$photo_url = $item->photo->size_variants?->getThumb()?->url;
		}

		return new self(
			purchasable_id: $item->id,
			album_id: $item->album_id,
			album_title: $album_title,
			photo_id: $item->photo_id,
			photo_title: $photo_title,
			photo_url: $photo_url,
			prices: $item->prices->map(PriceResource::fromModel(...))->toArray(),
			description: $item->description,
			owner_notes: $item->owner_notes,
			is_active: $item->is_active,
		);
	}
}
