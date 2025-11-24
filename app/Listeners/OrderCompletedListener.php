<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Listeners;

use App\Enum\PaymentStatusType;
use App\Enum\PurchasableSizeVariantType;
use App\Events\OrderCompleted;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * OrderCompletedListener - Post-payment order fulfillment processor.
 *
 * This event listener handles the automatic fulfillment of orders after payment
 * completion. It processes each order item to determine content availability and
 * automatically closes orders when all items can be delivered immediately.
 *
 * Key Responsibilities:
 * 1. Links order items to their corresponding size variants for download
 * 2. Identifies items requiring manual processing (FULL variants)
 * 3. Automatically closes orders when all content is ready for delivery
 * 4. Maintains order status consistency throughout the fulfillment process
 *
 * Order Item Processing Logic:
 * - MEDIUM/MEDIUM2X/ORIGINAL: Links to existing size variants if available
 * - FULL: Requires manual photographer processing, cannot be auto-fulfilled
 * - Items with existing content_url or size_variant_id are skipped (already processed)
 *
 * Order Closure Rules:
 * - Order status changes to CLOSED only when ALL items have available content
 * - Orders with FULL variants or missing size variants remain in COMPLETED status
 * - CLOSED status indicates the order is fully fulfilled and ready for customer access
 *
 * This listener is triggered by the OrderCompleted event, typically fired when
 * payment processing is successfully completed by a payment provider.
 *
 * @see OrderCompleted The event that triggers this listener
 * @see Order The order model being processed
 * @see PaymentStatusType Order status enumeration
 * @see PurchasableSizeVariantType Size variant enumeration
 */
class OrderCompletedListener
{
	/**
	 * Handle the OrderCompleted event to process order fulfillment.
	 *
	 * This method performs automatic order fulfillment by linking order items
	 * to their corresponding downloadable content. The process involves:
	 *
	 * 1. **Order Validation**: Ensures the order exists and loads required relationships
	 * 2. **Item Processing**: For each order item, attempts to link to size variants
	 * 3. **Content Linking**: Maps size variant types to actual photo size variants
	 * 4. **Delivery Check**: Determines if all items can be delivered immediately
	 * 5. **Status Update**: Closes the order if all content is available
	 *
	 * Size Variant Mapping:
	 * - MEDIUM → photo.size_variants.getMedium()
	 * - MEDIUM2X → photo.size_variants.getMedium2x()
	 * - ORIGINAL → photo.size_variants.getOriginal()
	 * - FULL → null (requires manual processing)
	 *
	 * The method uses bitwise AND operations to track delivery status efficiently.
	 * If any item cannot be fulfilled automatically (FULL variants or missing
	 * size variants), the order remains in COMPLETED status for manual processing.
	 *
	 * Error Handling:
	 * - Logs errors if the order cannot be found
	 * - Gracefully handles missing photos or size variants
	 * - Skips items that are already processed (have content_url or size_variant_id)
	 *
	 * @param OrderCompleted $event Event containing the order ID to process
	 *
	 * @return void
	 */
	public function handle(OrderCompleted $event): void
	{
		$order = Order::with('items', 'items.photo', 'items.photo.size_variants')->find($event->order_id);
		if ($order === null) {
			Log::error('OrderCompletedListener: Order not found', ['order_id' => $event->order_id]);

			return;
		}

		$this->fullfillOrder($order);
	}

	/**
	 * Fulfill an order by linking items to downloadable content.
	 *
	 * This is the core fulfillment logic that processes each order item and
	 * attempts to link it to its corresponding downloadable size variant. The
	 * method can be called either automatically (via the handle() event method)
	 * or manually (via maintenance tasks like FullfillOrders).
	 *
	 * Processing Flow:
	 * 1. Iterates through all order items
	 * 2. Skips items already fulfilled (have content_url or size_variant_id)
	 * 3. Maps size variant type to actual photo size variant
	 * 4. Updates order item with size_variant_id for downloadable content
	 * 5. Tracks whether ALL items can be delivered
	 * 6. Closes order if all items are successfully linked
	 *
	 * Size Variant Resolution:
	 * - MEDIUM: Links to photo's medium size variant (typically 1920px)
	 * - MEDIUM2X: Links to photo's medium2x size variant (typically 3840px)
	 * - ORIGINAL: Links to photo's original size variant (full resolution)
	 * - FULL: Returns null, requires manual photographer processing
	 *
	 * Fulfillment Status Tracking:
	 * Uses bitwise AND logic to track if all items can be delivered:
	 * - Starts as true
	 * - Becomes false if ANY item's variant is null
	 * - Only closes order if remains true after all items processed
	 *
	 * Order Status Transitions:
	 * - COMPLETED → CLOSED: All items successfully linked to variants
	 * - COMPLETED → COMPLETED: At least one item cannot be auto-fulfilled
	 *
	 * Items That Cannot Be Auto-Fulfilled:
	 * - FULL size variants (require manual processing)
	 * - Missing size variants (not yet generated)
	 * - Deleted photos (photo relationship is null)
	 *
	 * Items That Are Skipped:
	 * - Already have content_url (custom download link provided)
	 * - Already have size_variant_id (previously processed)
	 *
	 * Usage Scenarios:
	 * 1. **Automatic**: Called by handle() when OrderCompleted event fires
	 * 2. **Manual**: Called by FullfillOrders maintenance task for retry logic
	 * 3. **Batch**: Called for multiple orders during maintenance operations
	 *
	 * Side Effects:
	 * - Modifies order_items.size_variant_id for fulfilled items
	 * - May change orders.status from COMPLETED to CLOSED
	 * - Updates orders.updated_at timestamp when status changes
	 *
	 * No Exceptions Thrown:
	 * - Gracefully handles missing photos (variant will be null)
	 * - Gracefully handles missing size variants (variant will be null)
	 * - Does not fail the entire order if one item cannot be fulfilled
	 *
	 * @param Order $order The order to fulfill (must have items relationship loaded)
	 *
	 * @return void
	 */
	public function fullfillOrder(Order $order): void
	{
		// Track whether all items in the order can be delivered immediately
		$all_items_delivered = true;

		// Process each order item to link it with downloadable content
		foreach ($order->items as $item) {
			// Skip items that already have content assigned (previously processed)
			if ($item->content_url !== null || $item->size_variant_id !== null) {
				continue;
			}

			// Map the purchased size variant type to the actual photo size variant
			// FULL variants return null as they require manual photographer processing
			$variant = match ($item->size_variant_type) {
				PurchasableSizeVariantType::MEDIUM => $item->photo?->size_variants->getMedium(),
				PurchasableSizeVariantType::MEDIUM2x => $item->photo?->size_variants->getMedium2x(),
				PurchasableSizeVariantType::ORIGINAL => $item->photo?->size_variants->getOriginal(),
				PurchasableSizeVariantType::FULL => null, // Requires manual processing
			};

			// Update delivery status: false if any variant is null (unavailable)
			$all_items_delivered = $all_items_delivered && $variant !== null;

			// Link the order item to the size variant for download access
			$item->size_variant_id = $variant?->id ?? null;
			$item->save();
		}

		// Close the order only if all items can be delivered immediately
		// Orders with FULL variants or missing content remain in COMPLETED status
		if ($all_items_delivered === true) {
			$order->status = PaymentStatusType::CLOSED;
			$order->save();
		}
	}
}
