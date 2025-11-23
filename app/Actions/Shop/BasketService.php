<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Shop;

use App\Enum\PaymentStatusType;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Exceptions\Shop\BasketDeletionFailedException;
use App\Exceptions\Shop\OrderIsNotPendingException;
use App\Models\Album;
use App\Models\Order;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Facades\Log;

/**
 * The difference between a basket and an order is that a basket is still pending
 * and can be modified, while an order is completed and cannot be changed anymore.
 *
 * This service provides methods to manage the basket, including creating a new basket,
 */
class BasketService
{
	public function __construct(
		private OrderService $order_service,
		private PurchasableService $purchasable_service,
		private AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * Guard method to ensure the basket is in a pending state.
	 *
	 * @param Order $basket The basket to check
	 *
	 * @throws OrderIsNotPendingException If the basket is not in pending state
	 */
	protected function ensurePendingStatus(Order $basket): void
	{
		if ($basket->status !== PaymentStatusType::PENDING) {
			throw new OrderIsNotPendingException($basket->id);
		}
	}

	/**
	 * Get an existing basket by ID or create a new one if ID is null.
	 *
	 * @param Order|null $basket existing basket or null to create new
	 * @param User|null  $user   Optional user to associate with the basket
	 *
	 * @return Order The basket order
	 */
	public function getOrCreateBasket(?Order $basket, ?User $user = null): Order
	{
		if ($basket !== null) {
			// If user is now logged in but basket wasn't associated with a user
			if ($user !== null && $basket->user_id === null) {
				$basket->user_id = $user->id;
				$basket->email = $user->email;
				$basket->save();
			}

			return $basket;
		}

		return $this->order_service->createOrder($user);
	}

	/**
	 * Add a photo to the basket.
	 *
	 * @param Order                      $basket       The basket to add to
	 * @param Photo                      $photo        The photo to add
	 * @param string                     $album_id     The album ID the photo belongs to
	 * @param PurchasableSizeVariantType $size_variant The size variant
	 * @param PurchasableLicenseType     $license_type The license type
	 * @param string|null                $notes        Optional notes for the item
	 *
	 * @return Order The updated basket
	 *
	 * @throws \Exception If the photo is not available for purchase
	 */
	public function addPhotoToBasket(
		Order $basket,
		Photo $photo,
		string $album_id,
		PurchasableSizeVariantType $size_variant,
		PurchasableLicenseType $license_type,
		?string $notes = null,
	): Order {
		$this->ensurePendingStatus($basket);
		$basket = $this->order_service->addPhotoToOrder(
			$basket,
			$photo,
			$album_id,
			$size_variant,
			$license_type,
			$notes
		);

		return $this->order_service->refreshBasket($basket);
	}

	/**
	 * Add an album to the basket.
	 *
	 * @param Order                      $basket            The basket to add to
	 * @param Album                      $album             The album to add
	 * @param PurchasableSizeVariantType $size_variant      The size variant
	 * @param PurchasableLicenseType     $license_type      The license type
	 * @param string|null                $notes             Optional notes for the items
	 * @param bool                       $include_subalbums Whether to include photos from subalbums
	 *
	 * @return Order The updated basket
	 */
	public function addAlbumToBasket(
		Order $basket,
		Album $album,
		PurchasableSizeVariantType $size_variant,
		PurchasableLicenseType $license_type,
		?string $notes = null,
		bool $include_subalbums = false,
	): Order {
		$this->ensurePendingStatus($basket);

		// Select the list of accessible albums from current.
		// The reason why we do this selection before fetching purchasables
		// and then directly select the photos from the purchasable
		// is because when creating the OrderItem, we need to make sure that the
		// album_id is one that the user can actually see.
		// If we would just fetch the photos from the purchasables, we could end
		// up with photos that are purchasable but in albums that the user cannot see.
		// This would lead to inconsistencies in the OrderItems.
		if ($include_subalbums) {
			$albums_ids = $this->album_query_policy->applyBrowsabilityFilter(Album::query()->select('id'), $album->_lft, $album->_rgt)->pluck('id')->toArray();
		} else {
			$albums_ids = [$album->id];
		}

		// TODO: it is worth wondering if it is the full album that is bought or if it is just a collection of photos
		foreach ($albums_ids as $album_id) {
			// Get all purchasable photos in the album
			$photos = $this->purchasable_service->getPurchasablePhotosInAlbum($album_id);

			foreach ($photos as $photo) {
				try {
					$basket = $this->order_service->addPhotoToOrder(
						$basket,
						$photo,
						$album_id,
						$size_variant,
						$license_type,
						$notes
					);
					// @codeCoverageIgnoreStart
				} catch (\Exception $e) {
					// Continue with other photos even if one fails
					Log::warning('Failed to add photo to basket', [
						'photo_id' => $photo->id,
						'album_id' => $album_id,
						'error' => $e->getMessage(),
					]);
					continue;
				}
				// @codeCoverageIgnoreEnd
			}
		}

		return $this->order_service->refreshBasket($basket);
	}

	/**
	 * Remove an item from the basket.
	 *
	 * @param Order $basket  The basket to remove from
	 * @param int   $item_id The ID of the order item to remove
	 *
	 * @return Order The updated basket
	 *
	 * @throws \Exception If the item doesn't belong to the basket
	 */
	public function removeItemFromBasket(Order $basket, int $item_id): Order
	{
		$this->ensurePendingStatus($basket);

		$item = $basket->items()->find($item_id);
		if ($item === null) {
			throw new \Exception('Item not found in the current basket');
		}

		$item->delete();

		return $this->order_service->refreshBasket($basket);
	}

	/**
	 * Delete the entire basket.
	 *
	 * @param Order $basket The basket to delete
	 *
	 * @return bool True if the basket was deleted successfully
	 *
	 * @throws OrderIsNotPendingException    If the basket is not in pending state
	 * @throws BasketDeletionFailedException If deletion fails
	 */
	public function deleteBasket(Order $basket): bool
	{
		$this->ensurePendingStatus($basket);

		try {
			// Delete all items first
			$basket->items()->delete();
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			Log::error('Failed to delete basket items', [
				'basket_id' => $basket->id,
				'exception' => $e,
			]);
			throw new BasketDeletionFailedException($basket->id, 'Failed to delete basket items');
		}
		// @codeCoverageIgnoreEnd

		try {
			// Then delete the basket
			$basket->delete();
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			Log::error('Failed to delete basket items', [
				'basket_id' => $basket->id,
				'exception' => $e,
			]);
			throw new BasketDeletionFailedException($basket->id, 'Failed to delete basket record');
		}
		// @codeCoverageIgnoreEnd

		return true;
	}
}
