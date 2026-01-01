<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\OrderItem;
use App\Services\MoneyService;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class OrderItemResource extends Data
{
	public function __construct(
		public int $id,
		public int $order_id,
		public ?int $purchasable_id,
		public ?string $album_id,
		public ?string $photo_id,
		public string $title,
		public PurchasableLicenseType $license_type,
		public string $price,
		public PurchasableSizeVariantType $size_variant_type,
		public ?string $item_notes,
		public ?string $content_url,
	) {
	}

	/**
	 * @return OrderItemResource
	 */
	public static function fromModel(OrderItem $item): OrderItemResource
	{
		$money_service = resolve(MoneyService::class);

		return new self(
			id: $item->id,
			order_id: $item->order_id,
			purchasable_id: $item->purchasable_id,
			album_id: $item->album_id,
			photo_id: $item->photo_id,
			title: $item->title,
			license_type: $item->license_type,
			price: $money_service->format($item->price_cents),
			size_variant_type: $item->size_variant_type,
			item_notes: $item->item_notes,
			content_url: $item->content_url,
		);
	}
}
