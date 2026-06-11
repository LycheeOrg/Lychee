<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ShopManagement\ListPurchasablesRequest;
use App\Http\Requests\ShopManagement\PrintSize\CreatePrintSizeRequest;
use App\Http\Requests\ShopManagement\PrintSize\DeletePrintSizeRequest;
use App\Http\Requests\ShopManagement\PrintSize\UpdatePrintSizeRequest;
use App\Http\Resources\Shop\PrintSizeResource;
use App\Models\PrintSize;
use Illuminate\Routing\Controller;

/**
 * Admin CRUD controller for the global print size catalogue.
 */
class PrintSizeManagementController extends Controller
{
	/**
	 * List all print sizes (active and inactive).
	 *
	 * @return PrintSizeResource[]
	 */
	public function index(ListPurchasablesRequest $request): array
	{
		return PrintSizeResource::collect(PrintSize::all()->all());
	}

	/**
	 * Create a new print size in the global catalogue.
	 *
	 * @param CreatePrintSizeRequest $request
	 *
	 * @return PrintSizeResource
	 */
	public function store(CreatePrintSizeRequest $request): PrintSizeResource
	{
		$print_size = PrintSize::create([
			'label' => $request->label,
			'width' => $request->width,
			'height' => $request->height,
			'unit' => $request->unit,
			'paper_type' => $request->paper_type,
			'is_active' => $request->is_active,
		]);

		return PrintSizeResource::fromModel($print_size);
	}

	/**
	 * Update a print size in the global catalogue.
	 *
	 * @param UpdatePrintSizeRequest $request
	 *
	 * @return PrintSizeResource
	 */
	public function update(UpdatePrintSizeRequest $request): PrintSizeResource
	{
		$request->print_size->update([
			'label' => $request->label,
			'width' => $request->width,
			'height' => $request->height,
			'unit' => $request->unit,
			'paper_type' => $request->paper_type,
			'is_active' => $request->is_active,
		]);

		return PrintSizeResource::fromModel($request->print_size);
	}

	/**
	 * Delete a print size from the global catalogue.
	 * Blocked when any purchasable_print_sizes assignments still reference this size.
	 *
	 * @param DeletePrintSizeRequest $request
	 *
	 * @return void
	 */
	public function destroy(DeletePrintSizeRequest $request): void
	{
		$request->print_size->delete();
	}
}
