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
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Shop\InvalidPurchaseOptionException;
use App\Exceptions\Shop\PhotoNotPurchasableException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
	public function __construct(
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

	/**
	 * Clear old orders that are older than 2 weeks, have no items, and have no user_id.
	 *
	 * @return void
	 */
	public function clearOldOrders(): void
	{
		DB::transaction(function (): void {
			// Delete all the order items first to avoid foreign key constraint issues
			$chunk = $this->getQueryOldOrders()->pluck('id')->chunk(100);
			foreach ($chunk as $old_orders_ids) {
				OrderItem::whereIn('order_id', $old_orders_ids)->delete();
			}
			$this->getQueryOldOrders()->delete();
		});
	}

	/**
	 * Count the number of old orders.
	 *
	 * @return int
	 */
	public function countOldOrders(): int
	{
		return $this->getQueryOldOrders()->count();
	}

	/**
	 * Return the query builder for old orders.
	 *
	 * An old order is defined as being older than $weeks weeks,
	 * - having no user_id,
	 * - having no items
	 * - or having items but status is still PENDING
	 *
	 * @param int $weeks
	 *
	 * @return Builder
	 */
	protected function getQueryOldOrders(int $weeks = 2): Builder
	{
		$threshold_date = now()->subWeeks($weeks);

		return Order::where('created_at', '<', $threshold_date)
			->whereNull('user_id')
			->where(function (Builder $query): void {
				$query->where('status', PaymentStatusType::PENDING)
					->orWhereDoesntHave('items');
			});
	}

	/**
	 * Mark an offline order as paid (completed).
	 *
	 * @param Order $order The order to mark as paid
	 *
	 * @return Order The updated order
	 *
	 * @throws LycheeLogicException If the order is not in offline status
	 */
	public function markAsPaid(Order $order): Order
	{
		if ($order->status !== PaymentStatusType::OFFLINE) {
			throw new LycheeLogicException('Order must be in offline status to be marked as paid');
		}

		$order->status = PaymentStatusType::COMPLETED;
		$order->save();

		return $order;
	}

	/**
	 * Mark a completed order as delivered (closed).
	 *
	 * @param Order $order The order to mark as delivered
	 *
	 * @return Order The updated order
	 *
	 * @throws LycheeLogicException If the order is not in completed status
	 */
	public function markAsDelivered(Order $order): Order
	{
		if ($order->status !== PaymentStatusType::COMPLETED) {
			throw new LycheeLogicException('Order must be in completed status to be marked as delivered');
		}

		$order->status = PaymentStatusType::CLOSED;
		$order->save();

		return $order;
	}
}
