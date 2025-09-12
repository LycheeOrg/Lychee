<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class GetBasketRequest extends BaseApiRequest implements HasBasket
{
	protected ?Order $order = null;

	public function basket(): ?Order
	{
		return $this->order;
	}

	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::BASKET_ID_ATTRIBUTE => ['nullable', 'integer'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		// If there is a basket_id in the session, use it.
		$basket_id = Session::get(RequestAttribute::BASKET_ID_ATTRIBUTE, $values[RequestAttribute::BASKET_ID_ATTRIBUTE] ?? null);
		if ($basket_id !== null) {
			$this->order = Order::find($basket_id);
		}

		$user_id = Auth::id();
		if ($user_id === null) {
			return;
		}

		if ($this->order?->user_id !== $user_id) {
			// If the basket belongs to another user, ignore it.
			$this->order = null;
		}

		// If user is logged in, retrieve the current pending basket.
		$this->order ??= Order::where('user_id', $user_id)->where('status', PaymentStatusType::PENDING)->first();
	}
}
