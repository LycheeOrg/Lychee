<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Shop\OrderService;
use App\Enum\PaymentStatusType;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Listeners\OrderCompletedListener;
use App\Models\Order;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * FulfillOrders - Maintenance controller for order fulfillment retry logic.
 *
 * Processes orders that have been paid but not yet fulfilled. This serves as a
 * catch-all mechanism when automatic fulfillment fails.
 *
 * Common Scenarios:
 * - Missing size variants at fulfillment time
 * - System interruptions during fulfillment
 * - Auto-fulfillment disabled (webshop_auto_fulfill_enabled = false)
 * - Manual status changes without actual fulfillment
 *
 * Fulfillment Criteria:
 * An item is unfulfilled when both size_variant_id and download_link are NULL.
 * Orders in COMPLETED or CLOSED status with unfulfilled items are processed.
 *
 * Auto-Fulfilled: MEDIUM, MEDIUM2X, ORIGINAL (if size variants exist)
 * Manual Processing Required: FULL variants (photographer processing needed)
 *
 * Usage:
 * - Run after GenSizeVariants to link newly created variants
 * - Run when customers report missing download links
 * - Run periodically as health check
 *
 * @see OrderService Provides queries for unfulfilled orders
 * @see OrderCompletedListener Contains the actual fulfillment logic
 */
class FulfillOrders extends Controller
{
	/**
	 * @param OrderService           $order_service            Service for order queries and operations
	 * @param OrderCompletedListener $order_completed_listener Listener for order fulfillment
	 */
	public function __construct(
		protected OrderService $order_service,
		protected OrderCompletedListener $order_completed_listener,
	) {
	}

	/**
	 * Process all unfulfilled orders and attempt to fulfill them.
	 *
	 * Identifies orders with status COMPLETED or CLOSED that have unfulfilled items
	 * (size_variant_id and download_link both NULL), then links order items to their
	 * corresponding size variants. Orders are closed when all items are fulfilled.
	 *
	 * Process:
	 * 1. Single query with EXISTS clause finds orders with unfulfilled items
	 * 2. Eager loads: items, items.photo, items.photo.size_variants
	 * 3. Calls OrderCompletedListener.fulfillOrder() for each order
	 * 4. Links items to size variants (MEDIUM, MEDIUM2X, ORIGINAL)
	 * 5. Updates order status to CLOSED if all items are fulfilled
	 *
	 * What It Does:
	 * - Links order_items to existing size_variants
	 * - Updates order status to CLOSED when fully fulfilled
	 *
	 * What It Doesn't Do:
	 * - Generate missing size variants (run GenSizeVariants first)
	 * - Process FULL variants (requires manual intervention)
	 * - Send notifications or refunds
	 *
	 * Safe to run multiple times (idempotent). Already fulfilled items are skipped.
	 * Verify results by checking count before and after with check() method.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$orders = Order::with('items', 'items.photo', 'items.photo.size_variants')
			->whereIn('status', [PaymentStatusType::COMPLETED->value, PaymentStatusType::CLOSED->value])
			->whereExists(fn ($query) => $query->select(DB::raw(1))
				->from('order_items')
				->whereColumn('order_items.order_id', 'orders.id')
				->whereNull('size_variant_id')
				->whereNull('download_link'))
			->get();

		foreach ($orders as $order) {
			$this->order_completed_listener->fullfillOrder($order);
		}
	}

	/**
	 * Count the number of orders requiring fulfillment.
	 *
	 * Returns the count of COMPLETED or CLOSED orders with at least one unfulfilled item
	 * (where both size_variant_id and download_link are NULL).
	 *
	 * Interpreting Results:
	 * - 0: All orders properly fulfilled
	 * - 1-10: Small backlog (possibly FULL variants or recent orders)
	 * - 11-50: Moderate backlog (investigate root cause)
	 * - 50+: Systemic issue (check auto-fulfillment, size variants, logs)
	 *
	 * Typical Workflow:
	 * 1. Call check() to see current count
	 * 2. Run GenSizeVariants if size variants are missing
	 * 3. Call do() to process fulfillment
	 * 4. Call check() again to verify count decreased
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return int Total number of orders with unfulfilled items
	 */
	public function check(MaintenanceRequest $request): int
	{
		return $this->order_service->selectCompleteOrderNeedingFulfillmentQuery()->count() + $this->order_service->selectClosedOrderNeedingFulfillmentQuery()->count();
	}
}