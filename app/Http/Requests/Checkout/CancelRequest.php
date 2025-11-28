<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Fetched from the url.
 *
 * @property string $transaction_id
 *
 * @method merge(array $values)
 * @method route(string $key)
 */
class CancelRequest extends BaseApiRequest implements HasBasket
{
	protected Order $order;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order?->status === PaymentStatusType::PROCESSING;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::TRANSACTION_ID_ATTRIBUTE => ['required', 'string'],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::TRANSACTION_ID_ATTRIBUTE => $this->route(RequestAttribute::TRANSACTION_ID_ATTRIBUTE),
		]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$order = Order::findByTransactionId($values[RequestAttribute::TRANSACTION_ID_ATTRIBUTE]);
		if ($order === null) {
			throw new ModelNotFoundException('Order not found.');
		}
		$this->order = $order;
	}

	public function basket(): ?Order
	{
		return $this->order;
	}
}
