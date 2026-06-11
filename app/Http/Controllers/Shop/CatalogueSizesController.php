<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Http\Requests\Catalog\GetCatalogueSizesRequest;
use App\Http\Resources\Shop\CatalogueSizesResource;
use App\Http\Resources\Shop\PurchasablePixelSizeResource;
use App\Http\Resources\Shop\PurchasablePrintSizeResource;
use App\Models\PurchasablePixelSize;
use App\Models\PurchasablePrintSize;
use Illuminate\Routing\Controller;

/**
 * Customer-facing controller that returns active print and pixel sizes with prices for a purchasable.
 */
class CatalogueSizesController extends Controller
{
	/**
	 * Return active print/pixel sizes (with prices) assigned to a purchasable.
	 *
	 * @param GetCatalogueSizesRequest $request
	 *
	 * @return CatalogueSizesResource
	 */
	public function sizes(GetCatalogueSizesRequest $request): CatalogueSizesResource
	{
		$purchasable = $request->purchasable;

		$print_sizes = PurchasablePrintSize::with('printSize')
			->where('purchasable_id', $purchasable->id)
			->whereHas('printSize', fn ($q) => $q->where('is_active', true))
			->get()
			->map(fn (PurchasablePrintSize $pps) => PurchasablePrintSizeResource::fromModel($pps))
			->values()
			->all();

		$pixel_sizes = PurchasablePixelSize::with('pixelSize')
			->where('purchasable_id', $purchasable->id)
			->whereHas('pixelSize', fn ($q) => $q->where('is_active', true))
			->get()
			->map(fn (PurchasablePixelSize $pps) => PurchasablePixelSizeResource::fromModel($pps))
			->values()
			->all();

		return new CatalogueSizesResource(
			print_sizes: $print_sizes,
			pixel_sizes: $pixel_sizes,
		);
	}
}
