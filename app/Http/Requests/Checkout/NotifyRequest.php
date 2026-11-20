<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Actions\Shop\Gateway\Async\AsyncPaymentRegistry;
use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use App\Rules\OmnipayProviderTypeRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Server-to-server payment notification for an order.
 *
 * The order is fetched from the url by its id — the immutable key the
 * notification URL was built with at purchase time, which the gateway
 * retries deliveries to. The notification body itself is verified
 * cryptographically by the provider's strategy in
 * AsyncPaymentGateway::handleNotification().
 *
 * @property string $order_id
 * @property string $provider
 *
 * @method merge(array $values)
 * @method route(string $key)
 */
class NotifyRequest extends BaseApiRequest implements HasBasket
{
	protected OmnipayProviderType $provider_type;
	protected Order $order;

	/**
	 * Determine if the sender is authorized to make this request.
	 *
	 * Only providers that settle asynchronously receive notifications.
	 *
	 * CANCELLED is allowed on purpose: a buyer can abandon the browser flow
	 * and still pay from the hosted checkout page that is already open — the
	 * money moved, so the notification must be able to complete the order.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order?->provider === $this->provider_type &&
			resolve(AsyncPaymentRegistry::class)->isAsync($this->provider_type) &&
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
			RequestAttribute::PROVIDER_ATTRIBUTE => ['required', new OmnipayProviderTypeRule(false)],
			RequestAttribute::ORDER_ID_ATTRIBUTE => ['required', 'string'],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::PROVIDER_ATTRIBUTE => $this->route(RequestAttribute::PROVIDER_ATTRIBUTE),
			RequestAttribute::ORDER_ID_ATTRIBUTE => $this->route(RequestAttribute::ORDER_ID_ATTRIBUTE),
		]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$order = Order::find($values[RequestAttribute::ORDER_ID_ATTRIBUTE]);
		if ($order === null) {
			throw new ModelNotFoundException('Order not found.');
		}
		$this->order = $order;
		$this->provider_type = OmnipayProviderType::from($values[RequestAttribute::PROVIDER_ATTRIBUTE]);
	}

	public function basket(): ?Order
	{
		return $this->order;
	}

	public function provider_type(): ?OmnipayProviderType
	{
		return $this->provider_type;
	}
}
