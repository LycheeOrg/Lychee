<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Shop\PurchasableService;
use App\Http\Requests\ShopManagement\DeletePurchasablesRequest;
use App\Http\Requests\ShopManagement\PurchasableAlbumRequest;
use App\Http\Requests\ShopManagement\PurchasablePhotoRequest;
use App\Http\Requests\ShopManagement\UpdatePurchasablePriceRequest;
use App\Http\Resources\Shop\PurchasableResource;
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
	 * Set photos as purchasable.
	 *
	 * @param PurchasablePhotoRequest $request
	 *
	 * @return PurchasableResource[]
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

		return PurchasableResource::collect($purchasables);
	}

	/**
	 * Set albums as purchasable.
	 *
	 * @param PurchasableAlbumRequest $request
	 *
	 * @return PurchasableResource[]
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

		return PurchasableResource::collect($purchasables);
	}

	/**
	 * Update the prices for a purchasable item.
	 *
	 * @param UpdatePurchasablePriceRequest $request
	 *
	 * @return PurchasableResource
	 */
	public function updatePurchasablePrices(UpdatePurchasablePriceRequest $request): PurchasableResource
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

		return PurchasableResource::fromModel($purchasable);
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