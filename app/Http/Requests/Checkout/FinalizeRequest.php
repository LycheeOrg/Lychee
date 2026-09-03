<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use App\Rules\OmnipayProviderTypeRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Fetched from the url.
 *
 * @property string $transaction_id
 * @property string $provider
 *
 * @method merge(array $values)
 * @method route(string $key)
 */
class FinalizeRequest extends BaseApiRequest implements HasBasket
{
	protected OmnipayProviderType $provider_type;
	protected Order $order;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		// Asynchronous providers can complete the order from the payment
		// notification before the buyer's browser returns; that return is
		// still legitimate and must be able to show the completed order.
		$is_valid_status = $this->order?->status === PaymentStatusType::PROCESSING ||
			($this->order?->status === PaymentStatusType::COMPLETED && $this->provider_type === OmnipayProviderType::PAYZUM);

		return $is_valid_status && $this->order?->provider === $this->provider_type && $this->provider_type !== null;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PROVIDER_ATTRIBUTE => ['required', new OmnipayProviderTypeRule(false)],
			RequestAttribute::TRANSACTION_ID_ATTRIBUTE => ['required', 'string'],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::PROVIDER_ATTRIBUTE => $this->route(RequestAttribute::PROVIDER_ATTRIBUTE),
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
