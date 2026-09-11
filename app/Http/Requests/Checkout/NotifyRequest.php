<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Server-to-server payment notification for an order.
 *
 * The order is fetched from the url by its id — completing an order replaces
 * its transaction id with the provider reference, and the gateway retries
 * notifications to the URL it was given at purchase time. The notification
 * body itself is verified cryptographically by the payment gateway driver in
 * CheckoutService::handlePaymentNotification().
 *
 * @property string $order_id
 *
 * @method merge(array $values)
 * @method route(string $key)
 */
class NotifyRequest extends BaseApiRequest implements HasBasket
{
	public const ORDER_ID_ATTRIBUTE = 'order_id';

	protected Order $order;

	/**
	 * Determine if the sender is authorized to make this request.
	 *
	 * CANCELLED is allowed on purpose: a buyer can abandon the browser flow
	 * and still pay from the hosted checkout page that is already open — the
	 * money moved, so the notification must be able to complete the order.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order?->provider === OmnipayProviderType::PAYZUM &&
			in_array($this->order?->status, [
				PaymentStatusType::PROCESSING,
				PaymentStatusType::CANCELLED,
				PaymentStatusType::COMPLETED,
				PaymentStatusType::CLOSED,
			], true);
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			self::ORDER_ID_ATTRIBUTE => ['required', 'string'],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			self::ORDER_ID_ATTRIBUTE => $this->route(self::ORDER_ID_ATTRIBUTE),
		]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$order = Order::find($values[self::ORDER_ID_ATTRIBUTE]);
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
