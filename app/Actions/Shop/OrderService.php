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
use App\Events\OrderCompleted;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Shop\InvalidPurchaseOptionException;
use App\Exceptions\Shop\PhotoNotPurchasableException;
use App\Models\Configs;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * OrderService - Core business logic for order management and lifecycle.
 *
 * This service handles the complete lifecycle of photo purchase orders within
 * the Lychee webshop system. It provides methods for creating orders, adding
 * items, managing order states, and maintaining order data integrity.
 *
 * Key Responsibilities:
 * - Order creation with proper initialization (UUID transaction IDs, user association)
 * - Order item management (adding photos with pricing, size variants, and licenses)
 * - Order state transitions (PENDING → OFFLINE/COMPLETED → CLOSED)
 * - Order fulfillment queries (identifying orders needing content delivery)
 * - Order cleanup (removing abandoned carts and expired orders)
 * - Order retrieval with proper authorization (admin vs user scope)
 *
 * Order Status Flow:
 * 1. PENDING: Initial state when order is created
 * 2. OFFLINE: Manual payment method selected, awaiting confirmation
 * 3. COMPLETED: Payment received, awaiting fulfillment
 * 4. CLOSED: Payment received AND all items fulfilled
 *
 * The service integrates with:
 * - PurchasableService: Determines pricing and availability
 * - OrderCompletedListener: Handles post-payment fulfillment
 * - Payment providers: Processes transactions (external integration)
 *
 * @see Order The order model
 * @see OrderItem The order item model
 * @see PurchasableService Handles pricing and photo availability
 * @see OrderCompletedListener Fulfills orders after payment
 * @see PaymentStatusType Order status enumeration
 */
class OrderService
{
	public function __construct(
		private PurchasableService $purchasable_service,
	) {
	}

	/**
	 * Create a new order with initial PENDING status.
	 *
	 * Initializes a new order in the system with a unique transaction ID.
	 * The order starts in PENDING status and can be associated with either
	 * an authenticated user or a guest (anonymous) purchaser.
	 *
	 * For authenticated users:
	 * - Sets user_id and copies email from user profile
	 * - Links order to user account for order history tracking
	 *
	 * For guest purchases:
	 * - Leaves user_id as null
	 * - Email will be captured during checkout process
	 *
	 * The transaction_id is a UUID that serves as:
	 * - Unique identifier for payment provider integration
	 * - Reference for payment confirmation and reconciliation
	 * - Customer-facing order reference number
	 *
	 * @param User|null   $user    Associated user (optional, null for guest orders)
	 * @param string|null $comment Additional notes for the order (internal use)
	 *
	 * @return Order The created order with PENDING status
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
	 * Add a photo item to an order with pricing and licensing.
	 *
	 * This method adds a photo purchase to an existing order by:
	 * 1. Determining the effective purchasable configuration (album hierarchy)
	 * 2. Calculating the price based on size variant and license type
	 * 3. Creating an order item with all purchase details
	 *
	 * Pricing is hierarchical - the method uses PurchasableService to find
	 * the most specific purchasable configuration by traversing the album
	 * tree from the photo's location up to the root.
	 *
	 * Size Variants:
	 * - MEDIUM: Standard web resolution (typically 1920px)
	 * - MEDIUM2X: High-DPI web resolution (typically 3840px)
	 * - ORIGINAL: Full resolution with EXIF data preserved
	 * - FULL: Photographer-processed full resolution file
	 *
	 * License Types:
	 * - PERSONAL: Non-commercial personal use only
	 * - COMMERCIAL: Commercial use permitted
	 * - EXTENDED: Extended rights including resale/redistribution
	 *
	 * The order total is NOT automatically recalculated. Use refreshBasket()
	 * after adding items to update the order total.
	 *
	 * @param Order                      $order        The order to add to
	 * @param Photo                      $photo        The photo to add
	 * @param string                     $album_id     The album ID to consider for hierarchical pricing
	 * @param PurchasableSizeVariantType $size_variant The size variant (MEDIUM, MEDIUM2X, FULL, ORIGINAL)
	 * @param PurchasableLicenseType     $license_type The license type (PERSONAL, COMMERCIAL, EXTENDED)
	 * @param string|null                $notes        Additional notes for the item (customer requests)
	 *
	 * @return Order The updated Order (note: total not recalculated)
	 *
	 * @throws PhotoNotPurchasableException   If the photo is not available for purchase
	 * @throws InvalidPurchaseOptionException If the size/license combination is not available
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

	/**
	 * Refresh basket data and recalculate order total.
	 *
	 * This method should be called after adding or removing items from an order
	 * to ensure the order total reflects the current items. It performs two operations:
	 *
	 * 1. Refreshes the order model from the database (loads latest data)
	 * 2. Recalculates the total_cents by summing all order item prices
	 *
	 * Typical usage pattern:
	 * ```php
	 * $order = $orderService->createOrder($user);
	 * $orderService->addPhotoToOrder($order, $photo1, $album_id, $size, $license);
	 * $orderService->addPhotoToOrder($order, $photo2, $album_id, $size, $license);
	 * $order = $orderService->refreshBasket($order); // Recalculates total
	 * ```
	 *
	 * @param Order $basket The order to refresh and recalculate
	 *
	 * @return Order The refreshed order with updated total_cents
	 */
	public function refreshBasket(Order $basket): Order
	{
		$basket->refresh();
		$basket->updateTotal();

		return $basket;
	}

	/**
	 * Retrieve all orders with proper authorization scoping.
	 *
	 * Returns orders based on user permissions:
	 * - Administrators: See all orders from all users
	 * - Regular users: See only their own orders
	 * - Guests: See no orders (requires authentication)
	 *
	 * The query excludes order items by default for performance, but includes
	 * user relationships for displaying order ownership. Results are sorted by
	 * most recently updated first.
	 *
	 * Note: This method currently loads all orders at once. Pagination should
	 * be implemented for large datasets to avoid memory issues.
	 *
	 * @return array<int,Order> All orders accessible to the current user
	 */
	public function getAll(): array
	{
		$user = Auth::user();

		return Order::without(['items'])->with(['user'])
			->when($user?->may_administrate !== true, function ($query) use ($user): void {
				$query->where('user_id', $user?->id);
			})
			->orderBy('updated_at', 'desc')->get()->all();
	}

	/**
	 * Clear abandoned guest orders older than 2 weeks.
	 *
	 * This maintenance method removes old, incomplete orders to keep the database
	 * clean. It targets "abandoned cart" scenarios where guest users started an
	 * order but never completed it.
	 *
	 * An order is considered eligible for deletion if ALL conditions are met:
	 * - Created more than 2 weeks ago
	 * - Has no associated user_id (guest order)
	 * - Has no items OR status is still PENDING
	 *
	 * Orders with user_id are preserved regardless of age, allowing users to
	 * review their order history.
	 *
	 * The deletion is performed in a database transaction for data integrity:
	 * 1. Deletes order items in batches of 100 (prevents memory issues)
	 * 2. Deletes the parent orders
	 * 3. Rolls back if any errors occur
	 *
	 * This method is typically called by the FlushOldOrders maintenance task.
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
	 * Count abandoned guest orders eligible for deletion.
	 *
	 * Returns the number of orders that would be deleted by clearOldOrders().
	 * Uses the same criteria as clearOldOrders() to ensure count accuracy.
	 *
	 * This is useful for:
	 * - Displaying cleanup statistics in admin interface
	 * - Determining if cleanup is necessary
	 * - Monitoring cart abandonment rates
	 *
	 * @return int Number of old orders that can be safely deleted
	 */
	public function countOldOrders(): int
	{
		return $this->getQueryOldOrders()->count();
	}

	/**
	 * Build query for identifying abandoned guest orders.
	 *
	 * Constructs a query builder that selects orders meeting the "old order"
	 * criteria. This method is used internally by both countOldOrders() and
	 * clearOldOrders() to ensure consistent behavior.
	 *
	 * Deletion Criteria (ALL must be true):
	 * 1. Order created before threshold date (default: 2 weeks ago)
	 * 2. No user_id (guest order, not authenticated user)
	 * 3. Either:
	 *    - Status is PENDING (never progressed to payment), OR
	 *    - Has no items (empty cart)
	 *
	 * Why these criteria:
	 * - Guest orders are temporary by nature (no user account)
	 * - PENDING status indicates no payment attempt was made
	 * - Empty carts have no value to preserve
	 * - 2 week threshold balances cleanup with grace period
	 *
	 * Orders with user_id are NEVER included, preserving order history
	 * for authenticated users regardless of status or age.
	 *
	 * @param int $weeks Age threshold in weeks (default: 2)
	 *
	 * @return Builder Query builder for old orders
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
	 * Mark an offline order as paid and trigger fulfillment.
	 *
	 * This method handles the manual confirmation of payment for orders using
	 * offline payment methods (bank transfer, check, cash, etc.). It transitions
	 * the order from OFFLINE status to COMPLETED status.
	 *
	 * Process:
	 * 1. Validates order is in OFFLINE status
	 * 2. Updates order status to COMPLETED
	 * 3. Conditionally dispatches OrderCompleted event for auto-fulfillment
	 *
	 * Auto-fulfillment behavior:
	 * - If webshop_auto_fulfill_enabled = true: OrderCompleted event is dispatched
	 * - OrderCompletedListener handles automatic content delivery
	 * - Order may transition to CLOSED if all items can be fulfilled immediately
	 *
	 * If auto-fulfillment is disabled:
	 * - Order remains in COMPLETED status
	 * - Administrator must manually fulfill via FulfillOrders maintenance task
	 *
	 * Usage Scenario:
	 * Customer selects "bank transfer" payment method:
	 * 1. Order created with PENDING status
	 * 2. Customer selects offline payment → status becomes OFFLINE
	 * 3. Administrator confirms payment received → calls markAsPaid()
	 * 4. Status becomes COMPLETED (and possibly CLOSED if auto-fulfilled)
	 *
	 * @param Order $order The order to mark as paid (must be OFFLINE status)
	 *
	 * @return Order The updated order with COMPLETED status
	 *
	 * @throws LycheeLogicException If the order is not in OFFLINE status
	 */
	public function markAsPaid(Order $order): Order
	{
		if ($order->status !== PaymentStatusType::OFFLINE) {
			throw new LycheeLogicException('Order must be in offline status to be marked as paid');
		}

		$order->markAsPaid($order->transaction_id);

		// Dispatch the OrderCompleted event to fulfill post-order actions
		OrderCompleted::dispatchIf(Configs::getValueAsBool('webshop_auto_fulfill_enabled'), $order->id);

		return $order;
	}

	/**
	 * Manually mark a completed order as delivered (closed).
	 *
	 * This method provides a manual override to close orders that have been
	 * fulfilled but did not automatically transition to CLOSED status. It should
	 * be used when:
	 *
	 * - Auto-fulfillment is disabled
	 * - FULL variant orders have been manually processed
	 * - Content has been delivered outside the system
	 * - Administrator confirms all items have been provided to customer
	 *
	 * Status Transition:
	 * COMPLETED → CLOSED (or CLOSED → CLOSED for idempotency)
	 *
	 * The CLOSED status indicates:
	 * - Payment has been received
	 * - All order items have been fulfilled/delivered
	 * - Customer has access to purchased content
	 * - No further action required
	 *
	 * This method does NOT:
	 * - Verify items are actually fulfilled
	 * - Send notifications to customer
	 * - Generate download links
	 * - Link size variants to order items
	 *
	 * It is purely a status update and should only be used when the
	 * administrator has independently verified fulfillment is complete.
	 *
	 * @param Order $order The order to mark as delivered (must be COMPLETED or CLOSED status)
	 *
	 * @return Order The updated order with CLOSED status
	 *
	 * @throws LycheeLogicException If the order is not in COMPLETED or CLOSED status
	 */
	public function markAsDelivered(Order $order): Order
	{
		if ($order->status !== PaymentStatusType::COMPLETED && $order->status !== PaymentStatusType::CLOSED) {
			throw new LycheeLogicException('Order must be in completed or closed status to be marked as delivered');
		}

		$order->status = PaymentStatusType::CLOSED;
		$order->save();

		return $order;
	}

	/**
	 * Query builder for COMPLETED orders with unfulfilled items.
	 *
	 * Returns a query builder that selects orders in COMPLETED status where at least
	 * one order item has not been fulfilled. An item is considered unfulfilled when
	 * BOTH conditions are true:
	 * - size_variant_id is NULL (not linked to downloadable content)
	 * - download_link is NULL (no custom download URL provided)
	 *
	 * This query is used by:
	 * - FulfillOrders maintenance task (identifies orders needing processing)
	 * - Admin dashboard (displays fulfillment statistics)
	 * - Order management interface (shows pending fulfillments)
	 *
	 * Why COMPLETED orders need fulfillment:
	 * - Payment received but content not yet linked
	 * - Size variants didn't exist at payment time
	 * - Auto-fulfillment failed or was disabled
	 * - FULL variants awaiting manual processing
	 *
	 * Expected behavior:
	 * - Returns query builder (not executed)
	 * - Can be chained with additional constraints
	 * - Uses EXISTS subquery for efficiency
	 *
	 * @return Builder Query builder for COMPLETED orders needing fulfillment
	 */
	public function selectCompleteOrderNeedingFulfillmentQuery(): Builder
	{
		return Order::where('status', '=', PaymentStatusType::COMPLETED->value)
			->whereExists(fn ($query) => $query->select(DB::raw(1))
				->from('order_items')
				->whereColumn('order_items.order_id', 'orders.id')
				->whereNull('size_variant_id')
				->whereNull('download_link'));
	}

	/**
	 * Query builder for CLOSED orders with unfulfilled items.
	 *
	 * Returns a query builder that selects orders in CLOSED status where at least
	 * one order item has not actually been fulfilled. This represents a data
	 * inconsistency where the order was prematurely marked as closed.
	 *
	 * An item is considered unfulfilled when BOTH conditions are true:
	 * - size_variant_id is NULL (not linked to downloadable content)
	 * - download_link is NULL (no custom download URL provided)
	 *
	 * Why CLOSED orders might have unfulfilled items:
	 * - Manual status override without actual fulfillment
	 * - Database inconsistency from migration or import
	 * - Bug in fulfillment logic that marked order as closed prematurely
	 * - Race condition during fulfillment process
	 *
	 * This query helps identify orders that need corrective action:
	 * - Re-run fulfillment process to link content
	 * - Verify customer actually has access to content
	 * - Investigate why order was closed without fulfillment
	 *
	 * The FulfillOrders maintenance task processes both COMPLETED and CLOSED
	 * orders to ensure data consistency and customer satisfaction.
	 *
	 * @return Builder Query builder for CLOSED orders needing fulfillment
	 */
	public function selectClosedOrderNeedingFulfillmentQuery(): Builder
	{
		return Order::where('status', '=', PaymentStatusType::CLOSED->value)
			->whereExists(fn ($query) => $query->select(DB::raw(1))
				->from('order_items')
				->whereColumn('order_items.order_id', 'orders.id')
				->whereNull('size_variant_id')
				->whereNull('download_link'));
	}
}
