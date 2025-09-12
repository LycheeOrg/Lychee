<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\PaymentStatusType;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait HasBasketTrait
{
	protected ?Order $order = null;

	/**
	 * @return Order|null
	 */
	public function basket(): ?Order
	{
		return $this->order;
	}

	/**
	 * Resolve the basket from the session and prepare it for validation.
	 *
	 * @return void
	 */
	protected function prepareBasket(): void
	{
		// If there is a basket_id in the session, use it.
		$basket_id = Session::get(RequestAttribute::BASKET_ID_ATTRIBUTE);
		if ($basket_id !== null) {
			$this->order = Order::find($basket_id);
		}

		// If user is logged in, retrieve the current pending basket.
		$user_id = Auth::id();
		if ($user_id !== null && $this->order === null) {
			$this->order = Order::where('user_id', $user_id)->where('status', PaymentStatusType::PENDING)->first();
		}
	}
}
