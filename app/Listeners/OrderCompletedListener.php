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
			$all_items_delivered &= $variant !== null;

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
