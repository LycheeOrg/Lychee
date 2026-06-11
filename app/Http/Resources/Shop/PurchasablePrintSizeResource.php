<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\PurchasablePrintSize;
use App\Services\MoneyService;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a per-purchasable print size assignment including price.
 */
#[TypeScript()]
class PurchasablePrintSizeResource extends Data
{
	public function __construct(
		public int $id,
		public int $print_size_id,
		public string $label,
		public int $width,
		public int $height,
		public string $unit,
		public ?string $paper_type,
		public string $price,
		public int $price_cents,
	) {
	}

	/**
	 * @return PurchasablePrintSizeResource
	 */
	public static function fromModel(PurchasablePrintSize $purchasable_print_size): self
	{
		$money_service = resolve(MoneyService::class);

		return new self(
			id: $purchasable_print_size->id,
			print_size_id: $purchasable_print_size->print_size_id,
			label: $purchasable_print_size->printSize->label,
			width: $purchasable_print_size->printSize->width,
			height: $purchasable_print_size->printSize->height,
			unit: $purchasable_print_size->printSize->unit,
			paper_type: $purchasable_print_size->printSize->paper_type,
			price: $money_service->format($purchasable_print_size->price_cents),
			price_cents: intval($purchasable_print_size->price_cents->getAmount()),
		);
	}
}
