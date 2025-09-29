<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Shop\PurchasableService;
use App\Http\Requests\ShopManagement\DeletePurchasablesRequest;
use App\Http\Requests\ShopManagement\ListPurchasablesRequest;
use App\Http\Requests\ShopManagement\PurchasableAlbumRequest;
use App\Http\Requests\ShopManagement\PurchasablePhotoRequest;
use App\Http\Requests\ShopManagement\UpdatePurchasablePriceRequest;
use App\Http\Resources\Shop\ConfigOptionResource;
use App\Http\Resources\Shop\EditablePurchasableResource;
use App\Models\Purchasable;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for setting albums/photos as purchasable or not.
 */
class ShopManagementController extends Controller
{
	public function __construct(private PurchasableService $purchasable_service)
	{
	}

	/**
	 * Get shop management configuration options.
	 * This returns the configuration settings needed for shop management operations.
	 *
	 * @return ConfigOptionResource The shop management configuration options
	 */
	public function options(ListPurchasablesRequest $request): ConfigOptionResource
	{
		return new ConfigOptionResource();
	}

	/**
	 * List all purchasable items.
	 * This returns all purchasables in the system for management purposes.
	 *
	 * @return EditablePurchasableResource[] The list of all purchasable items
	 */
	public function list(ListPurchasablesRequest $request): array
	{
		$purchasables = Purchasable::with(['album', 'photo', 'prices', 'photo.size_variants'])
			->when(count($request->albumIds()) > 0, function ($query) use ($request): void {
				$query->whereIn('album_id', $request->albumIds());
			})
			->get();

		return EditablePurchasableResource::collect($purchasables->all());
	}

	/**
	 * Set photos as purchasable.
	 *
	 * @param PurchasablePhotoRequest $request
	 *
	 * @return EditablePurchasableResource[]
	 */
	public function setPhotoPurchasable(PurchasablePhotoRequest $request): array
	{
		$purchasables = [];
		foreach ($request->photos() as $photo) {
			$purchasables[] = $this->purchasable_service->createPurchasableForPhoto(
				photo: $photo,
				album_id: $request->album()->id,
				description: $request->description(),
				prices: $request->prices,
				owner_notes: $request->notes
			);
		}

		return EditablePurchasableResource::collect($purchasables);
	}

	/**
	 * Set albums as purchasable.
	 *
	 * @param PurchasableAlbumRequest $request
	 *
	 * @return EditablePurchasableResource[]
	 */
	public function setAlbumPurchasable(PurchasableAlbumRequest $request): array
	{
		$purchasables = [];
		foreach ($request->albums() as $album) {
			$purchasables[] = $this->purchasable_service->createPurchasableForAlbum(
				album: $album,
				description: $request->description(),
				applies_to_subalbums: $request->applies_to_subalbums,
				prices: $request->prices,
				owner_notes: $request->notes
			);
		}

		return EditablePurchasableResource::collect($purchasables);
	}

	/**
	 * Update the prices for a purchasable item.
	 *
	 * @param UpdatePurchasablePriceRequest $request
	 *
	 * @return EditablePurchasableResource
	 */
	public function updatePurchasablePrices(UpdatePurchasablePriceRequest $request): EditablePurchasableResource
	{
		$purchasable = $this->purchasable_service->updatePrices(
			purchasable: $request->purchasable,
			prices: $request->prices
		);

		// If there's a description or notes update, we need to update those too
		$updated = false;
		if ($request->description() !== null) {
			$purchasable->description = $request->description();
			$updated = true;
		}
		if ($request->notes !== null) {
			$purchasable->owner_notes = $request->notes;
			$updated = true;
		}
		if ($updated) {
			$purchasable->save();
		}

		return EditablePurchasableResource::fromModel($purchasable);
	}

	/**
	 * Delete purchasable items.
	 *
	 * @param DeletePurchasablesRequest $request
	 *
	 * @return void
	 */
	public function deletePurchasables(DeletePurchasablesRequest $request): void
	{
		foreach ($request->purchasables as $purchasable) {
			$this->purchasable_service->deletePurchasable($purchasable);
		}
	}
}