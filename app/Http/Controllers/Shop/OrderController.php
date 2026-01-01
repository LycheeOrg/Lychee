<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Actions\Shop\OrderService;
use App\Events\OrderCompleted;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\ListOrderRequest;
use App\Http\Requests\Order\MarkAsDeliveredOrderRequest;
use App\Http\Requests\Order\MarkAsPaidOrderRequest;
use App\Http\Resources\Shop\OrderResource;
use App\Models\OrderItem;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;

/**
 * Controller responsible for listing the orders.
 */
class OrderController extends Controller
{
	public function __construct(
		private OrderService $order_service,
	) {
	}

	/**
	 * List all orders in the system.
	 * This returns all orders in the system for management purposes.
	 *
	 * @return array<int,OrderResource> The list of all orders
	 */
	public function list(ListOrderRequest $request): array
	{
		return OrderResource::collect($this->order_service->getAll());
	}

	/**
	 * Given a Order request, return the order.
	 *
	 * @return OrderResource
	 */
	public function get(GetOrderRequest $request): OrderResource
	{
		return OrderResource::fromModel($request->order);
	}

	/**
	 * Mark an order as paid.
	 *
	 * @param MarkAsPaidOrderRequest $request
	 *
	 * @return void
	 */
	public function markAsPaid(MarkAsPaidOrderRequest $request): void
	{
		$this->order_service->markAsPaid($request->order);
	}

	/**
	 * Mark an order as delivered.
	 *
	 * @param MarkAsDeliveredOrderRequest $request
	 *
	 * @return void
	 */
	public function markAsDelivered(MarkAsDeliveredOrderRequest $request): void
	{
		if (count($request->items) > 0) {
			$key_name = 'id';
			$order_item_instance = new OrderItem();
			batch()->update($order_item_instance, $request->items, $key_name);
		}

		OrderCompleted::dispatchIf($request->configs()->getValueAsBool('webshop_manual_fulfill_enabled'), $request->order->id);

		$this->order_service->markAsDelivered($request->order);
	}

	/**
	 * Simple end point to delete existing cookies.
	 *
	 * @return void
	 */
	public function forget(): void
	{
		Cookie::queue(Cookie::forget('basket_id'));
	}
}
