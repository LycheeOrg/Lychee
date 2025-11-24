<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Shop\OrderService;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Listeners\OrderCompletedListener;
use App\Models\Order;
use Illuminate\Routing\Controller;

/**
 * FullfillOrders - Maintenance controller for order fulfillment retry logic.
 *
 * This maintenance controller provides administrative tools to identify and process
 * orders that have been paid but not yet fulfilled. It serves as a "catch-all"
 * mechanism to ensure customers receive their purchased content even when automatic
 * fulfillment fails or is incomplete.
 *
 * What Gets Fulfilled:
 * - Orders in COMPLETED status with unfulfilled items (payment received, content pending)
 * - Orders in CLOSED status with unfulfilled items (data inconsistency correction)
 *
 * An item is considered unfulfilled when BOTH conditions are true:
 * - size_variant_id is NULL (not linked to downloadable content)
 * - download_link is NULL (no custom download URL provided)
 *
 * @see OrderService Provides queries for unfulfilled orders
 * @see OrderCompletedListener Contains the actual fulfillment logic
 * @see Order The order model being processed
 * @see OrderItem Items within orders requiring fulfillment
 */
class FullfillOrders extends Controller
{
	/**
	 * Create a new FullfillOrders controller instance.
	 *
	 * Dependencies:
	 * - OrderService: Provides query builders for identifying unfulfilled orders
	 * - OrderCompletedListener: Contains the fulfillment logic for processing orders
	 *
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
	 * This method is the primary action of the maintenance task. It identifies
	 * all orders that have been paid but not fully fulfilled, then attempts to
	 * complete the fulfillment process by linking order items to downloadable
	 * size variants.
	 *
	 * Query Strategy:
	 * Uses two separate queries to identify problematic orders:
	 * 1. CLOSED orders with unfulfilled items (data inconsistency)
	 * 2. COMPLETED orders with unfulfilled items (normal retry scenario)
	 *
	 * The queries are combined with orWhereIn() to process both types in a
	 * single batch, with eager loading of all necessary relationships to
	 * optimize database performance.
	 *
	 * Process Flow:
	 * 1. Queries for orders with status CLOSED or COMPLETED that have items where:
	 *    - size_variant_id is NULL
	 *    - download_link is NULL
	 * 2. Eager loads: items, items.photo, items.photo.size_variants
	 * 3. Iterates through each order
	 * 4. Calls OrderCompletedListener.fullfillOrder() to process each order
	 * 5. Each order item is linked to its size variant if available
	 * 6. Order status changes to CLOSED if all items are fulfilled
	 *
	 * What This Method Does:
	 * - Links order items to existing size variants
	 * - Updates order_items.size_variant_id for fulfilled items
	 * - Changes orders.status to CLOSED for fully fulfilled orders
	 * - Updates orders.updated_at timestamp
	 *
	 * What This Method Does NOT Do:
	 * - Generate missing size variants (run GenSizeVariants first)
	 * - Process FULL variants (requires manual photographer intervention)
	 * - Send customer notifications (handled separately)
	 * - Refund orders that cannot be fulfilled
	 *
	 * Expected Outcomes:
	 * - Success: Items linked to size variants, order status becomes CLOSED
	 * - Partial: Some items fulfilled, order remains COMPLETED
	 * - No Change: Size variants still missing or FULL variants present
	 *
	 * Performance Considerations:
	 * - Uses eager loading to prevent N+1 query problems
	 * - Processes all orders in a single batch (no pagination)
	 * - For large datasets (100+ orders), consider monitoring execution time
	 * - Safe to run multiple times (idempotent - already fulfilled items are skipped)
	 *
	 * Error Handling:
	 * - Gracefully handles missing photos (items remain unfulfilled)
	 * - Gracefully handles missing size variants (items remain unfulfilled)
	 * - Does not throw exceptions for individual order failures
	 * - Check application logs for specific error details
	 *
	 * After Running:
	 * - Verify count decreases with check() method
	 * - Review logs for any errors during processing
	 * - Check customer accounts to ensure download access
	 * - Investigate persistent unfulfilled items for root cause
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return void No return value (check logs for processing details)
	 */
	public function do(MaintenanceRequest $request): void
	{
		$orders = Order::with('items', 'items.photo', 'items.photo.size_variants')
			->whereIn('orders.id', $this->order_service->selectClosedOrderNeedingFulfillmentQuery()->select('orders.id'))
			->orWhereIn('orders.id', $this->order_service->selectCompleteOrderNeedingFulfillmentQuery()->select('orders.id'))
			->get();

		foreach ($orders as $order) {
			$this->order_completed_listener->fullfillOrder($order);
		}
	}

	/**
	 * Count the number of orders requiring fulfillment.
	 *
	 * This method returns the total count of orders that have unfulfilled items,
	 * serving as a diagnostic tool to determine if maintenance action is needed.
	 *
	 * Calculation:
	 * Sums two separate counts:
	 * 1. Orders with status COMPLETED and unfulfilled items
	 * 2. Orders with status CLOSED and unfulfilled items
	 *
	 * An order is included in the count if it has at least one order item where
	 * BOTH conditions are true:
	 * - size_variant_id is NULL (not linked to downloadable content)
	 * - download_link is NULL (no custom download URL provided)
	 *
	 * Interpreting the Results:
	 * - 0: All orders are properly fulfilled, system is healthy
	 * - 1-10: Small number of unfulfilled orders, possibly FULL variants or recent orders
	 * - 11-50: Moderate backlog, consider investigating root cause
	 * - 50+: Significant issue, likely systemic problem (auto-fulfillment disabled,
	 *        missing size variants, or listener failures)
	 *
	 * Use Cases:
	 * - Display in admin dashboard as a health indicator
	 * - Determine if do() method needs to be executed
	 * - Monitor fulfillment system effectiveness over time
	 * - Validate maintenance task results (should decrease after running do())
	 * - Alert administrators when count exceeds threshold
	 *
	 * Why Orders May Need Fulfillment:
	 * - Auto-fulfillment disabled (webshop_auto_fullfill_enabled = false)
	 * - Size variants not yet generated (need to run GenSizeVariants)
	 * - FULL variants awaiting manual processing (expected behavior)
	 * - OrderCompletedListener errors during automatic fulfillment
	 * - Database inconsistencies from migrations or manual changes
	 *
	 * Workflow:
	 * 1. Call check() to see current count
	 * 2. If count > 0, investigate why (check logs, size variants, FULL items)
	 * 3. Run GenSizeVariants if size variants are missing
	 * 4. Call do() to process fulfillment
	 * 5. Call check() again to verify count decreased
	 * 6. If count unchanged, investigate FULL variants or other blockers
	 *
	 * Performance:
	 * - Executes two separate COUNT queries (efficient)
	 * - Uses EXISTS subquery (optimized for large datasets)
	 * - No data loading, only counting (fast operation)
	 * - Safe to call frequently for monitoring
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return int Total number of orders with unfulfilled items (COMPLETED + CLOSED)
	 */
	public function check(MaintenanceRequest $request): int
	{
		return $this->order_service->selectCompleteOrderNeedingFulfillmentQuery()->count() + $this->order_service->selectClosedOrderNeedingFulfillmentQuery()->count();
	}
}