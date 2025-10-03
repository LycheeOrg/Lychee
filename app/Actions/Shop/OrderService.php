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
use App\Exceptions\Shop\InvalidPurchaseOptionException;
use App\Exceptions\Shop\PhotoNotPurchasableException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderService
{
	public function __construct(
		private MoneyService $money_service,
		private PurchasableService $purchasable_service,
	) {
	}

	/**
	 * Create a new order.
	 *
	 * @param User|null   $user    Associated user (optional)
	 * @param string|null $comment Additional notes for the order
	 *
	 * @return Order The created order
	 */
	public function createOrder(?User $user = null, ?string $comment = null): Order
	{
		return Order::create([
			'transaction_id' => Str::uuid(),
			'provider' => null,
			'user_id' => $user?->id,
			'email' => $user?->email,
			'status' => PaymentStatusType::PENDING,
			'comment' => $comment,
		]);
	}

	/**
	 * Add a photo item to an order.
	 *
	 * @param Order                      $order        The order to add to
	 * @param Photo                      $photo        The photo to add
	 * @param string                     $album_id     The album ID to consider for hierarchical pricing
	 * @param PurchasableSizeVariantType $size_variant The size variant (MEDIUM, FULL, ORIGINAL)
	 * @param PurchasableLicenseType     $license_type The license type (PERSONAL, COMMERCIAL, EXTENDED)
	 * @param string|null                $notes        Additional notes for the item
	 *
	 * @return Order The updated Order
	 *
	 * @throws \Exception If the photo is not available for purchase
	 */
	public function addPhotoToOrder(
		Order $order,
		Photo $photo,
		string $album_id,
		PurchasableSizeVariantType $size_variant,
		PurchasableLicenseType $license_type,
		?string $notes = null,
	): Order {
		$purchasable = $this->purchasable_service->getEffectivePurchasableForPhoto($photo, $album_id);

		if ($purchasable === null) {
			throw new PhotoNotPurchasableException();
		}

		$price = $purchasable->getPriceFor($size_variant, $license_type);

		if ($price === null) {
			throw new InvalidPurchaseOptionException();
		}

		// Create the order item
		OrderItem::create([
			'order_id' => $order->id,
			'purchasable_id' => $purchasable->id,
			'photo_id' => $photo->id,
			'album_id' => $album_id,
			'title' => $photo->title ?? "Photo #{$photo->id}",
			'license_type' => $license_type,
			'price_cents' => $price,
			'size_variant_type' => $size_variant,
			'item_notes' => $notes,
		]);

		return $order;
	}

	public function refreshBasket(Order $basket): Order
	{
		$basket->refresh();
		$basket->updateTotal();

		return $basket;
	}

	/**
	 * List all the orders in the DB.
	 * Add pagination later.
	 *
	 * @return array<int,Order>
	 */
	public function getAll(): array
	{
		$user = Auth::user();

		return Order::without(['items'])->with(['user'])
			->when($user?->may_administrate !== true, function ($query) use ($user): void {
				$query->where('user_id', $user?->id);
			})
			->orderBy('id', 'desc')->get()->all();
	}
}
