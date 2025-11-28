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
use Illuminate\Support\Facades\Cookie;

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
	 * Resolve the basket from the cookie and prepare it for validation.
	 *
	 * @return void
	 */
	protected function prepareBasket(): void
	{
		// If there is a basket_id in the cookie, use it.
		$basket_id = Cookie::get(RequestAttribute::BASKET_ID_ATTRIBUTE);
		if ($basket_id !== null && $basket_id !== '') {
			$this->order = Order::find(intval($basket_id));
		}

		// Validate basket is not of another user.
		$user_id = Auth::id();
		if (
			$user_id !== null &&
			$this->order !== null &&
			$this->order->user_id !== null &&
			$this->order->user_id !== $user_id
		) {
			$this->order = null;
			Cookie::queue(Cookie::forget(RequestAttribute::BASKET_ID_ATTRIBUTE));
		}

		// If user is logged in, retrieve the current pending basket.
		if ($user_id !== null && $this->order === null) {
			$this->order = Order::where('user_id', $user_id)->where('status', PaymentStatusType::PENDING)->first();
		}
	}
}
