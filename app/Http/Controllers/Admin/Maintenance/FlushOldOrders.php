<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Shop\OrderService;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use Illuminate\Routing\Controller;

/**
 * We count the number of old orders and can flush them.
 */
class FlushOldOrders extends Controller
{
	public function __construct(protected OrderService $order_service)
	{
	}

	/**
	 * Delete old orders.
	 */
	public function do(MaintenanceRequest $request): void
	{
		$this->order_service->clearOldOrders();
	}

	/**
	 * Count the number of old orders.
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		return $this->order_service->countOldOrders();
	}
}