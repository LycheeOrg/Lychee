<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ShopManagement\ListPurchasablesRequest;
use App\Http\Requests\ShopManagement\PixelSize\CreatePixelSizeRequest;
use App\Http\Requests\ShopManagement\PixelSize\DeletePixelSizeRequest;
use App\Http\Requests\ShopManagement\PixelSize\UpdatePixelSizeRequest;
use App\Http\Resources\Shop\PixelSizeResource;
use App\Models\PixelSize;
use Illuminate\Routing\Controller;

/**
 * Admin CRUD controller for the global pixel size catalogue.
 */
class PixelSizeManagementController extends Controller
{
	/**
	 * List all pixel sizes (active and inactive).
	 *
	 * @return PixelSizeResource[]
	 */
	public function index(ListPurchasablesRequest $request): array
	{
		return PixelSizeResource::collect(PixelSize::all()->all());
	}

	/**
	 * Create a new pixel size in the global catalogue.
	 *
	 * @param CreatePixelSizeRequest $request
	 *
	 * @return PixelSizeResource
	 */
	public function store(CreatePixelSizeRequest $request): PixelSizeResource
	{
		$pixel_size = PixelSize::create([
			'label' => $request->label,
			'width' => $request->width,
			'height' => $request->height,
			'is_active' => $request->is_active,
		]);

		return PixelSizeResource::fromModel($pixel_size);
	}

	/**
	 * Update a pixel size in the global catalogue.
	 *
	 * @param UpdatePixelSizeRequest $request
	 *
	 * @return PixelSizeResource
	 */
	public function update(UpdatePixelSizeRequest $request): PixelSizeResource
	{
		$request->pixel_size->update([
			'label' => $request->label,
			'width' => $request->width,
			'height' => $request->height,
			'is_active' => $request->is_active,
		]);

		return PixelSizeResource::fromModel($request->pixel_size);
	}

	/**
	 * Delete a pixel size from the global catalogue.
	 * Blocked when any purchasable_pixel_sizes assignments still reference this size.
	 *
	 * @param DeletePixelSizeRequest $request
	 *
	 * @return void
	 */
	public function destroy(DeletePixelSizeRequest $request): void
	{
		$request->pixel_size->delete();
	}
}
