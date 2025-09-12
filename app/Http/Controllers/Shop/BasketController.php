<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Actions\Shop\BasketService;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Exceptions\Shop\BasketDeletionFailedException;
use App\Exceptions\Shop\OrderIsNotPendingException;
use App\Http\Requests\Basket\AddAlbumToBasketRequest;
use App\Http\Requests\Basket\AddPhotoToBasketRequest;
use App\Http\Requests\Basket\DeleteBasketRequest;
use App\Http\Requests\Basket\DeleteItemRequest;
use App\Http\Requests\Basket\GetBasketRequest;
use App\Http\Resources\Shop\OrderResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BasketController extends Controller
{
	public function __construct(
		private BasketService $basket_service,
	) {
	}

	/**
	 * Add a photo to the basket.
	 *
	 * @param AddPhotoToBasketRequest $request
	 *
	 * @return OrderResource
	 */
	public function addPhoto(AddPhotoToBasketRequest $request): OrderResource
	{
		$basket = $request->basket();

		$basket = $this->basket_service->addPhotoToBasket(
			$basket,
			$request->photo,
			$request->album_id,
			$request->size_variant,
			$request->license_type,
			$request->notes
		);

		return OrderResource::fromModel($basket);
	}

	/**
	 * Add all purchasable photos from an album to the basket.
	 *
	 * @param AddAlbumToBasketRequest $request
	 *
	 * @return OrderResource
	 */
	public function addAlbum(AddAlbumToBasketRequest $request): OrderResource
	{
		$basket = $request->basket();

		$basket = $this->basket_service->addAlbumToBasket(
			$basket,
			$request->album,
			$request->size_variant,
			$request->license_type,
			$request->notes,
			$request->include_subalbums
		);

		return OrderResource::fromModel($basket);
	}

	/**
	 * Remove an item from the basket.
	 *
	 * @param DeleteItemRequest $request
	 *
	 * @return OrderResource
	 */
	public function removeItem(DeleteItemRequest $request): OrderResource
	{
		$basket = $request->basket();

		$basket = $this->basket_service->removeItemFromBasket($basket, $request->item_id);

		return OrderResource::fromModel($basket);
	}

	/**
	 * Delete the entire basket.
	 *
	 * @param DeleteBasketRequest $request
	 *
	 * @return void
	 *
	 * @throws OrderIsNotPendingException    If the basket is not in pending state
	 * @throws BasketDeletionFailedException If deletion fails
	 */
	public function delete(DeleteBasketRequest $request): void
	{
		$basket = $request->basket();

		if ($basket === null) {
			// Basket is already gone, nothing to do
			return;
		}

		// The service will throw appropriate exceptions if deletion fails
		$this->basket_service->deleteBasket($basket);

		// Remove basket ID from session
		Session::forget(RequestAttribute::BASKET_ID);
	}

	/**
	 * Get the current basket.
	 *
	 * @param GetBasketRequest $request
	 *
	 * @return OrderResource
	 */
	public function get(GetBasketRequest $request): OrderResource
	{
		/** @var User $user */
		$user = Auth::user();
		$basket = $this->basket_service->getOrCreateBasket($request->basket(), $user);
		Session::put(RequestAttribute::BASKET_ID, $basket->id);

		return OrderResource::fromModel($basket);
	}
}
