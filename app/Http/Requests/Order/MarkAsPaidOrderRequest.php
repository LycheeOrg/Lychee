<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Order;

use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Mark an order as paid.
 */
class MarkAsPaidOrderRequest extends BaseApiRequest
{
	public Order $order;

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'order_id' => 'required|integer',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $order_id */
		$order_id = intval($values['order_id'] ?? null);
		$this->order = Order::query()
			->where('status', '=', PaymentStatusType::OFFLINE)
			->where('id', '=', $order_id)
			->firstOrFail();
	}
}
