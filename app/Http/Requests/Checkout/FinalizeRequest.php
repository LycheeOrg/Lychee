<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rules\Enum;

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
		return $this->order?->status === PaymentStatusType::PROCESSING && $this->order?->provider === $this->provider_type && $this->provider_type !== null;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PROVIDER_ATTRIBUTE => ['required', new Enum(OmnipayProviderType::class)],
			RequestAttribute::TRANSACTION_ID_ATTRIBUTE => ['required', 'string'],
		];
	}

	protected function prepareForValidation(): void
	{
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
