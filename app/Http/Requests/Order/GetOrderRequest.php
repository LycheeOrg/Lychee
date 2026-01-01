<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Retrieve an order for a given order id.
 *
 * The authorization check is done as follows:
 * - If the user is logged in, we check if the order belongs to the user.
 * - If the user is not logged in, we check if the order id matches the order's transaction id.
 *
 * @method merge(array $values)
 * @method route(string $key)
 */
class GetOrderRequest extends BaseApiRequest
{
	public Order $order;
	public string $transaction_id;

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			'order_id' => $this->route('order_id'),
		]);
		/** @disregard */
		if ($this->has('transaction_id') === false) {
			/** @disregard */
			$this->merge([
				'transaction_id' => '',
			]);
		} else {
			/** @disregard */
			$this->merge([
				'transaction_id' => strval($this->input('transaction_id')),
			]);
		}
	}

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		// Either we are admin or the order belongs to the user
		if ($user?->may_administrate === true ||
		($user !== null && $this->order->user_id === $user->id)) {
			return true;
		}

		if ($user !== null && $this->order->user_id !== $user->id && $this->transaction_id === '') {
			// If we are here, the user is logged in but the order does not belong to the user
			return false;
		}

		return $this->order->transaction_id === $this->transaction_id;
	}

	public function rules(): array
	{
		return [
			'order_id' => 'required|integer',
			'transaction_id' => 'sometimes|string|max:191',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $order_id */
		$order_id = intval($values['order_id'] ?? null);
		$this->order = Order::findOrFail($order_id);
		$this->transaction_id = strval($values['transaction_id'] ?? '');
	}
}