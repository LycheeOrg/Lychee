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
use App\Rules\StringRule;
use Illuminate\Support\Facades\Auth;

/**
 * Mark an order as delivered.
 */
class MarkAsDeliveredOrderRequest extends BaseApiRequest
{
	public Order $order;

	/** @var array<int,array{id:int,download_link:string}> */
	public array $items = [];

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		if ($user?->may_administrate !== true) {
			return false;
		}

		// All the elements of $this->items should be found it the items of order.
		return count(
			array_diff(
				array_column($this->items, 'id'),
				$this->order->items->pluck('id')->toArray()
			)
		) === 0;
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			'order_id' => $this->route('order_id'),
		]);
	}

	public function rules(): array
	{
		return [
			'order_id' => 'required|integer',
			'items' => 'sometimes|array',
			'items.*.id' => 'required|integer',
			'items.*.download_link' => ['required', new StringRule(false, 190)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $order_id */
		$order_id = intval($values['order_id'] ?? null);
		$this->order = Order::query()
			->where(fn($query) => $query
				->where('status', '=', PaymentStatusType::COMPLETED)
				->orWhere('status', '=', PaymentStatusType::CLOSED))
			->where('id', '=', $order_id)
			->firstOrFail();

		$this->items = $values['items'] ?? [];
	}
}
