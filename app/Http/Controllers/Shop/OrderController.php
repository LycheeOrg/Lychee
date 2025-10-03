<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Actions\Shop\OrderService;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\ListOrderRequest;
use App\Http\Resources\Shop\OrderResource;
use Illuminate\Routing\Controller;

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
}
