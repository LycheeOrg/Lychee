<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\PrintSize;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a global print size catalogue entry.
 * Price is not included here; per-purchasable prices are in PurchasablePrintSizeResource.
 */
#[TypeScript()]
class PrintSizeResource extends Data
{
	public function __construct(
		public int $id,
		public string $label,
		public int $width,
		public int $height,
		public string $unit,
		public ?string $paper_type,
		public bool $is_active,
	) {
	}

	/**
	 * @return PrintSizeResource
	 */
	public static function fromModel(PrintSize $print_size): self
	{
		return new self(
			id: $print_size->id,
			label: $print_size->label,
			width: $print_size->width,
			height: $print_size->height,
			unit: $print_size->unit,
			paper_type: $print_size->paper_type,
			is_active: $print_size->is_active,
		);
	}
}
